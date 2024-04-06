<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PPGroup\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderCancelAfterCoupon implements ObserverInterface
{
    protected $coupon;
    protected $saleRule;
    protected $_resource;
    private $logger;
    protected $request;

    public function __construct(
        \Magento\SalesRule\Model\Coupon $coupon,
        \Magento\SalesRule\Model\Rule $saleRule,
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->coupon = $coupon;
        $this->saleRule = $saleRule;
        $this->_resource = $resource;
        $this->logger = $logger;
        $this->request = $request;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if (!empty($observer->getOrder()->getCouponCode())) {
            $coupon = $observer->getOrder()->getCouponCode();
            $customerId = $observer->getOrder()->getCustomerId();
            $this->checkUpdateRule($coupon,  $customerId);
        }
    }

    public function checkUpdateRule($coupon,  $customerId)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/templog.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->addWriter($writer);

        $logger->info("==================Go go go! ========================= ");

        $logger->info("Pass case 1 - Cancel Order with coupon $coupon");
        $model =   $this->coupon->loadByCode($coupon);
        $ruleId = $model->getRuleId();
        $couponId = $model->getId();

        $connectionSalesruleCouponUsage = $this->_resource->getConnection();
        $select = $connectionSalesruleCouponUsage->select();

        try {
            $select->from(
                'salesrule_coupon_usage',
                ['times_used']
            )->where(
                'coupon_id = :coupon_id'
            )->where(
                'customer_id = :customer_id'
            );

            $timesUsed = $connectionSalesruleCouponUsage->fetchOne($select, [':coupon_id' => $couponId, ':customer_id' => $customerId]);

            $rule = $this->saleRule->load($ruleId);
            $timeUsesPerCustomer = $rule->getUsesPerCustomer();

            $moduleName = $this->request->getModuleName();
            $controller = $this->request->getControllerName();
            $action     = $this->request->getActionName();
            $route      = $this->request->getRouteName();

            $logger->info("Module: $moduleName, Controller: $controller, Action: $action, Route: $route ");

            /** check sql */
            $connectionSQL = $this->_resource->getConnection();
            $tableSQL = $connectionSQL->getTableName('salesrule_coupon_usage');
            $querySQL = "SELECT * FROM `" . $tableSQL . "` WHERE coupon_id = $couponId AND customer_id = $customerId ";
            $resultSQL = $connectionSQL->fetchAll($querySQL);
            $logger->info("Test SQL case " . json_encode($resultSQL));

            if ($timesUsed > 0) {
                $logger->info("Pass case 2 - Times Used > 0 ");
                if ($timeUsesPerCustomer == 1) {
                    $logger->info("Pass case 2.1 - Times Used == 1 ");
                    $this->_resource->getConnection()->update(
                        'salesrule_coupon_usage',
                        ['times_used' => 0],
                        ['coupon_id = ?' => $couponId, 'customer_id = ?' => $customerId]
                    );
                    $logger->info("Update table success! Coupon ID $couponId , Customer ID: $customerId ");
                } else {
                    $logger->info("Pass case 2.2 - Times Used > 1 ");
                    // $timeUsesPerCustomer > 1
                    $this->_resource->getConnection()->update(
                        'salesrule_coupon_usage',
                        ['times_used' =>  $timesUsed - 1],
                        ['coupon_id = ?' => $couponId, 'customer_id = ?' => $customerId]
                    );
                    $logger->info("Update table success! Coupon ID $couponId , Customer ID: $customerId ");
                }
            }
            $connectionSalesruleCustomer = $this->_resource->getConnection();

            $select = $connectionSalesruleCustomer->select()->from(
                'salesrule_customer'
            )->where(
                'customer_id = :customer_id'
            )->where(
                'rule_id = :rule_id'
            );

            $dataTimesUsed = $connectionSalesruleCustomer->fetchOne($select, [':rule_id' => $ruleId, ':customer_id' => $customerId]);
            if ($dataTimesUsed > 0) {
                $logger->info("Pass case 3 - Times Used > 0 ");

                $this->_resource->getConnection()->update(
                    'salesrule_customer',
                    ['times_used' => 0],
                    ['rule_id = ?' => $ruleId, 'customer_id = ?' => $customerId]
                );
                $logger->info("Update table success! Rule ID $ruleId , Customer ID: $customerId ");
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
