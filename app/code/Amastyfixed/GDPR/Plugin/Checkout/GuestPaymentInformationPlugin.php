<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amastyfixed\GDPR\Plugin\Checkout;

use Amasty\Base\Model\Serializer;
use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;

class GuestPaymentInformationPlugin
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    private $customerSession;

    public function __construct(
        Serializer $serializer,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession
    ) {
        $this->serializer = $serializer;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Checkout\Api\PaymentInformationManagementInterface $subject
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @throws NoSuchEntityException
     * @return void
     */
    public function beforeSavePaymentInformation(
        \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $additionalData = $paymentMethod->getAdditionalData();
        if (isset($additionalData[RegistryConstants::CONSENTS])
        ) {
            $this->customerSession->setCustomerEmail($email);
            $this->logConsents($additionalData[RegistryConstants::CONSENTS]);
        }
    }

    /**
     * @param array $codes
     *
     * @throws NoSuchEntityException
     */
    public function logConsents($codes)
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        if (!empty($codes)) {
            try {
                $codes = $this->serializer->unserialize($codes);
                $this->eventManager->dispatch(
                    'amasty_gdpr_consent_accept',
                    [
                        RegistryConstants::CONSENTS => $codes,
                        RegistryConstants::CONSENT_FROM => ConsentLogger::FROM_CHECKOUT,
                        RegistryConstants::STORE_ID => $storeId
                    ]
                );
            } catch (\Exception $e) {
                return;
            }
        }
    }
}
