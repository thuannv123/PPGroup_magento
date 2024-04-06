<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 * PHP version 5
 *
 * @category Acommerce_Sales
 * @package  Acommerce
 * @author   Por <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */

namespace Acommerce\Sales\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\SalesSequence\Model\Builder;
use Magento\SalesSequence\Model\Config as SequenceConfig;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * Export Sales Order To Cpms
 *
 * @category Acommerce_Sales
 * @package  Acommerce
 * @author   Por <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */
class InstallData implements InstallDataInterface
{
    //@codingStandardsIgnoreStart
    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * Sequence Builder
     *
     * @var Builder
     */
    private $sequenceBuilder;

    /**
     * Sequence Config
     *
     * @var SequenceConfig
     */
    private $sequenceConfig;
    //@codingStandardsIgnoreEnd

    /**
     * Construct
     *
     * @param SalesSetupFactory $salesSetupFactory Sales Setup Factory
     * @param Builder           $sequenceBuilder   Sequence Builder
     * @param SequenceConfig    $sequenceConfig    Sequence Config
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        Builder $sequenceBuilder,
        SequenceConfig $sequenceConfig
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->sequenceBuilder = $sequenceBuilder;
        $this->sequenceConfig = $sequenceConfig;
    }

    /**
     * Install
     *
     * @param ModuleDataSetupInterface $setup   Setup
     * @param ModuleContextInterface   $context Context
     *
     * @return voic
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $salesSetup->installEntities();

        $data = [];
        $statuses = [
            'invoiced' => __('Payment Received'),
            'shipped' => __('Shipped'),
        ];
        foreach ($statuses as $code => $info) {
            $data[] = ['status' => $code, 'label' => $info];
        }
        $setup->getConnection()->insertArray(
            $setup->getTable('sales_order_status'),
            ['status', 'label'],
            $data
        );

        $data = [];
        $states = [
            'invoiced' => [
                'label' => __('Payment Received'),
                'statuses' => ['invoiced' => ['default' => '1']],
            ],
            'shipped' => [
                'label' => __('Shipped'),
                'statuses' => ['shipped' => ['default' => '1']],
            ],
        ];

        foreach ($states as $code => $info) {
            if (isset($info['statuses'])) {
                foreach ($info['statuses'] as $status => $statusInfo) {
                    $isDefault = is_array($statusInfo)
                        && isset($statusInfo['default']) ? 1 : 0;

                    $data[] = [
                        'status' => $status,
                        'state' => $code,
                        'is_default' => $isDefault,
                        'visible_on_front' => 1,
                    ];
                }
            }
        }

        $setup->getConnection()->insertArray(
            $setup->getTable('sales_order_status_state'),
            ['status', 'state', 'is_default', 'visible_on_front'],
            $data
        );
    }
}