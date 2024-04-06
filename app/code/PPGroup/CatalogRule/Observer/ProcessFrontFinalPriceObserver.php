<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace PPGroup\CatalogRule\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session as CustomerModelSession;

/**
 * Observer for applying catalog rules on product for frontend area
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ProcessFrontFinalPriceObserver implements ObserverInterface
{
    /**
     * @var CustomerModelSession
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\RuleFactory
     */
    protected $resourceRuleFactory;

    /**
     * @var \Magento\CatalogRule\Observer\RulePricesStorage
     */
    protected $rulePricesStorage;

    /**
     * @param \Magento\CatalogRule\Observer\RulePricesStorage $rulePricesStorage
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleFactory $resourceRuleFactory
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param CustomerModelSession $customerSession
     */
    public function __construct(
        \Magento\CatalogRule\Observer\RulePricesStorage $rulePricesStorage,
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $resourceRuleFactory,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        CustomerModelSession $customerSession
    ) {
        $this->rulePricesStorage = $rulePricesStorage;
        $this->resourceRuleFactory = $resourceRuleFactory;
        $this->storeManager = $storeManager;
        $this->localeDate = $localeDate;
        $this->customerSession = $customerSession;
    }

    /**
     * Apply catalog price rules to product on frontend
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $pId = $product->getId();
        $storeId = $product->getStoreId();

        if ($observer->hasDate()) {
            $date = new \DateTime($observer->getEvent()->getDate());
        } else {
            $date = $this->localeDate->scopeDate($storeId);
        }

        if ($observer->hasWebsiteId()) {
            $wId = $observer->getEvent()->getWebsiteId();
        } else {
            $wId = $this->storeManager->getStore($storeId)->getWebsiteId();
        }

        if ($observer->hasCustomerGroupId()) {
            $gId = $observer->getEvent()->getCustomerGroupId();
        } elseif ($product->hasCustomerGroupId()) {
            $gId = $product->getCustomerGroupId();
        } else {
            $gId = $this->customerSession->getCustomerGroupId();
        }

        if ($pId == 8638) {
            $this->log("Current product id: " . $pId);
            $this->log("Current customer group: " . $gId);
        }



        $key = "{$date->format('Y-m-d H:i:s')}|{$wId}|{$gId}|{$pId}";
        if (!$this->rulePricesStorage->hasRulePrice($key)) {
            $rulePrice = $this->resourceRuleFactory->create()->getRulePrice($date, $wId, $gId, $pId);
            if ($pId == 8638) {
                $this->log("Current product rule price: " . $rulePrice);
            }
            $this->rulePricesStorage->setRulePrice($key, $rulePrice);
        }
        if ($this->rulePricesStorage->getRulePrice($key) !== false) {
            $finalPrice = min($product->getData('final_price'), $this->rulePricesStorage->getRulePrice($key));
            if ($pId == 8638) {
                $this->log("Current product final price: " . $finalPrice);
            }
            $product->setFinalPrice($finalPrice);
        }
        return $this;
    }

    public function log($data) {
        try {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/rule_price.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info($data);
        } catch (\Zend_Log_Exception $e) {

        }
    }
}
