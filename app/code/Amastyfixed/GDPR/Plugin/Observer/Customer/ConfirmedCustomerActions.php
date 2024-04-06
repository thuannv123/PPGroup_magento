<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amastyfixed\GDPR\Plugin\Observer\Customer;

use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\Gdpr\Model\ConsentLogger;

class ConfirmedCustomerActions
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        ManagerInterface $eventManager
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
    }

    /**
     * @param Observer $observer
     *
     * @throws NoSuchEntityException
     */
    public function aroundExecute(\Amasty\Gdpr\Observer\Customer\ConfirmedCustomerActions $subject, callable $proceed, $observer)
    {
        $consentsCodes = (array)$this->request->getParam(RegistryConstants::CONSENTS, []);
        $submittedFrom = $this->request->getParam(RegistryConstants::CONSENT_FROM);
        $customerId = null;

        if ($customer = $observer->getData('customer')) {
            $customerId = $customer->getId();
        }

        if($submittedFrom != ConsentLogger::FROM_PRIVACY_SETTINGS)
            $this->processConsentCodes($consentsCodes, $submittedFrom, $customerId);
    }

    /**
     * @param array $codes
     * @param string|null $from
     * @param int|null $customerId
     * @param int|null $storeId
     *
     * @throws NoSuchEntityException
     */
    protected function processConsentCodes(array $codes, $from, $customerId = null, $storeId = null)
    {
        $storeId = $storeId === null ? (int)$this->storeManager->getStore()->getId() : $storeId;

        if (!empty($codes) && $from) {
            $this->eventManager->dispatch(
                'amasty_gdpr_consent_accept',
                [
                    RegistryConstants::CONSENTS => $codes,
                    RegistryConstants::CONSENT_FROM => $from,
                    RegistryConstants::CUSTOMER_ID => $customerId,
                    RegistryConstants::STORE_ID => $storeId
                ]
            );
        }
    }
}