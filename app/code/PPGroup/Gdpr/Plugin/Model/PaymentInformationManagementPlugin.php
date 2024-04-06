<?php

namespace PPGroup\Gdpr\Plugin\Model;

use Amasty\Base\Model\Serializer;
use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Amasty\Gdpr\Model\ConsentLogger;
use Amasty\Gdpr\Observer\Checkout\ConsentRegistry;
use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;
use Psr\Log\LoggerInterface;

class PaymentInformationManagementPlugin
{
    /**
     * @var ConsentRegistry
     */
    private $consentRegistry;
    /**
     * @var Request
     */
    private $_request;
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    public function __construct(
        ConsentRegistry $consentRegistry,
        Serializer $serializer,
        LoggerInterface $logger,
        Request $request,
        OrderRepository $orderRepository,
        ManagerInterface $eventManager
    ) {
        $this->consentRegistry = $consentRegistry;
        $this->serializer = $serializer;
        $this->_request = $request;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->eventManager = $eventManager;
    }

    public function afterSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagement $subject,
        $result
    ) {
        if ($result) {
            try {
                $params = $this->_request->getBodyParams();
                if ($params['paymentMethod']) {
                    $paymentMethod = $params['paymentMethod'];
                    if (isset($paymentMethod['additional_data'])) {
                        if (isset($paymentMethod['additional_data'][RegistryConstants::CONSENTS])) {
                            $additionalInfo = $paymentMethod['additional_data'];
                            $serializedCodes = $additionalInfo[RegistryConstants::CONSENTS];
                            $this->consentRegistry->setConsents($this->serializer->unserialize($serializedCodes));
                        }
                        /** @var OrderInterface $order */
                        $order = $this->orderRepository->get($result);
                        $this->processConsentCodes(
                            $this->consentRegistry->getConsents(),
                            ConsentLogger::FROM_CHECKOUT,
                            (int)$order->getCustomerId(),
                            (int)$order->getStoreId(),
                            $order->getCustomerEmail()
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
        return $result;
    }

    /**
     * @param array $codes
     * @param string|null $from
     * @param int|null $customerId
     * @param int|null $storeId
     * @param string|null $email
     *
     * @throws NoSuchEntityException
     */
    protected function processConsentCodes(
        array $codes,
        ?string $from,
        ?int $customerId = null,
        ?int $storeId = null,
        ?string $email = null
    ): void {

        if (!empty($codes) && $from && $storeId) {
            $this->eventManager->dispatch(
                'amasty_gdpr_consent_accept',
                [
                    RegistryConstants::CONSENTS => $codes,
                    RegistryConstants::CONSENT_FROM => $from,
                    RegistryConstants::CUSTOMER_ID => $customerId,
                    RegistryConstants::STORE_ID => $storeId,
                    RegistryConstants::EMAIL => $email
                ]
            );
        }
    }
}
