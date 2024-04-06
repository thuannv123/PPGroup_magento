<?php

namespace PPGroup\Gdpr\Plugin\Model\DeleteRequest;

use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\DeleteRequest\Notifier;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

class NotifierPlugin
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var CustomerNameGenerationInterface
     */
    private $nameGeneration;

    /**
     * @var SenderResolverInterface
     */
    protected $senderResolver;

    /**
     * NotifierPlugin constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param Config $config
     * @param TransportBuilder $transportBuilder
     * @param CustomerNameGenerationInterface $nameGeneration
     * @param SenderResolverInterface $senderResolver
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Config $config,
        TransportBuilder $transportBuilder,
        CustomerNameGenerationInterface $nameGeneration,
        SenderResolverInterface $senderResolver
    ) {
        $this->customerRepository = $customerRepository;
        $this->config = $config;
        $this->transportBuilder = $transportBuilder;
        $this->nameGeneration = $nameGeneration;
        $this->senderResolver = $senderResolver;
    }

    /**
     * @param Notifier $subject
     * @param callable $proceed
     * @param $customerId
     * @param $comment
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function aroundNotify(
        Notifier $subject,
        callable $proceed,
        $customerId,
        $comment
    ) {
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            return;
        }

        $customerName = $this->nameGeneration->getCustomerName($customer);

        $template = $this->config->getValue('deletion_notification/deny_template', $customer->getStoreId());
        $sender = $this->config->getValue('deletion_notification/deny_sender', $customer->getStoreId());
        $replyTo = $this->config->getValue('deletion_notification/deny_reply_to', $customer->getStoreId());
        if (!trim($replyTo)) {
            $result = $this->senderResolver->resolve($sender);
            $replyTo = $result['email'];
        }

        $transport = $this->transportBuilder
            ->setTemplateIdentifier(
                $template
            )
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $customer->getStoreId()
                ]
            )
            ->setTemplateVars(
                [
                    'customer' => $customer,
                    'customerName' => $customerName,
                    'comment' => $comment
                ]
            )
            ->setFrom(
                $sender
            )
            ->addTo(
                $customer->getEmail(),
                $customerName
            )->setReplyTo(
                $replyTo
            )->getTransport();

        $transport->sendMessage();
    }

    /**
     * @param Notifier $subject
     * @param callable $proceed
     * @param $customerId
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function aroundNotifyAdmin(
        Notifier $subject,
        callable $proceed,
        $customerId
    ) {
        $customer = $this->customerRepository->getById($customerId);

        $customerName = $this->nameGeneration->getCustomerName($customer);

        $template = $this->config->getValue('deletion_notification/admin_template', $customer->getStoreId());
        $sender = $this->config->getValue('deletion_notification/admin_sender', $customer->getStoreId());
        $recievers = array_filter(preg_split('/\n|\r\n?/', $this->config->getValue('deletion_notification/admin_reciever', $customer->getStoreId())));

        foreach ($recievers as $reciever) {
            $transport = $this->transportBuilder->setTemplateIdentifier(
                $template
            )
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $customer->getStoreId()
                    ]
                )
                ->setTemplateVars(
                    [
                        'customerName' => $customerName
                    ]
                )
                ->setFrom(
                    $sender
                )
                ->addTo(
                    $reciever
                )
                ->getTransport();

            $transport->sendMessage();
        }
    }
}
