<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PPGroup\Ccpp\Override\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Api\OrderRepositoryInterface;
use Acommerce\Ccpp\Gateway\Request\HtmlRedirect\OrderDataBuilder;
use Acommerce\Ccpp\Gateway\Validator\DecisionValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Acommerce\Ccpp\Helper\P2c2pHash;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as OrderInvoiceCollectionFactory;


/**
 * Class ResponseCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResponseCommand extends \Acommerce\Ccpp\Gateway\Command\ResponseCommand
{
    const ACCEPT_COMMAND = 'accept_command';

    const CANCEL_COMMAND = 'cancel_command';

    /**
     * Transaction result codes map onto commands
     *
     * @var array
     */
    static private $commandsMap = [
        '000' => self::ACCEPT_COMMAND,
        '001' => self::CANCEL_COMMAND,
        '002' => self::CANCEL_COMMAND,
        '003' => self::CANCEL_COMMAND,
        '999' => self::CANCEL_COMMAND,
    ];

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $objConfigSettings;

    /**
     * @var P2c2pHash
     */
    protected $objP2c2pHashHelper;

    /**
     * @var OrderInvoiceCollectionFactory
     */
    protected $orderInvoiceCollectionFactory;

    /**
     * @param CommandPoolInterface     $commandPool
     * @param ValidatorInterface       $validator
     * @param OrderRepositoryInterface $orderRepository
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param Logger                   $logger
     * @param OrderInvoiceCollectionFactory $orderInvoiceCollectionFactory
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        ValidatorInterface $validator,
        OrderRepositoryInterface $orderRepository,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        ScopeConfigInterface $configSettings,
        P2c2pHash $p2c2pHash,
        Logger $logger,
        OrderInvoiceCollectionFactory $orderInvoiceCollectionFactory
    ) {
        $this->commandPool = $commandPool;
        $this->validator = $validator;
        $this->orderRepository = $orderRepository;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->logger = $logger;
        $this->orderInvoiceCollectionFactory = $orderInvoiceCollectionFactory;
        parent::__construct($commandPool, $validator, $orderRepository, $paymentDataObjectFactory, $configSettings, $p2c2pHash, $logger);
    }

    /**
     * @param array $commandSubject
     * @return \Magento\Payment\Gateway\Command\ResultInterface|void|null
     * @throws CommandException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute(array $commandSubject)
    {
        $this->logger->debug($commandSubject);

        $response = SubjectReader::readResponse($commandSubject);
        $result = $this->validator->validate($commandSubject);

        if (!$result->isValid()) {
            throw new CommandException(
                $result->getFailsDescription()
                    ? __(implode(', ', $result->getFailsDescription()))
                    : __('Gateway response is not valid.')
            );
        }

        $hashHelper   = $this->getHashHelper();
        $configHelper = $this->getConfigSettings();
        $isValidHash  = $hashHelper->isValidHashValue($_REQUEST,$configHelper['secret_key']);

        $order = $this->orderRepository->get((int)$response['user_defined_1']);

        //Check whether hash value is valid or not If not valid then redirect to home page when hash value is wrong.
        if(!$isValidHash) {
            $this->logger->debug(array('status' => 'invalid'));
            $this->logger->debug($_SERVER);
//            $order->setState(\Magento\Sales\Model\Order::STATUS_FRAUD);
//            $order->setStatus(\Magento\Sales\Model\Order::STATUS_FRAUD);
//            $order->save();
        }else{
//            $this->logger->debug(array('status' => 'valid'));
            $payment = $order->getPayment();

            $additionalPayment
                = $payment->getAdditionalInformation();
            $additionalPayment = array_merge(
                $additionalPayment,
                $response
            );

            if ($response['paid_agent'] == "123Service") {
                switch ($response['paid_channel']) {
                    case "IBANKING":
                    case "MOBILEBANKING":
                        if ($commandSubject['response']['channel_response_desc'] == "success (pending)"
                            && $response['payment_status'] == "001") {
                                $response['payment_status'] = "000";
                        }
                        break;
                    default :
                        break;
                }
            }

            if (isset($response['masked_pan'])) {
                $payment->setData('cc_number_enc', $response['masked_pan']);
                $payment->setData('cc_last_4', $response['masked_pan']);
            }

            $payment->setAdditionalInformation($additionalPayment);
            $payment->save();



            $actionCommandSubject = [
                'response' => $response,
                'payment' => $this->paymentDataObjectFactory->create(
                    $order->getPayment()
                )
            ];


            $command = $this->commandPool->get(
                self::$commandsMap[
                $response['payment_status']
                ]
            );

            $command->execute($actionCommandSubject);

            $orderInvoiceCollection = $this->orderInvoiceCollectionFactory->create()
                ->addFieldToFilter('order_id', $order->getEntityId());

            foreach ($orderInvoiceCollection->getItems() as $orderInvoice) {
                if ($orderInvoice->getGrandTotal() == 0) {
                    $orderInvoice->delete();
                }
            }
        }
    }
}
