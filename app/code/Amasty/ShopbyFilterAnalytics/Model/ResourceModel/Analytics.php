<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation by Amasty - Filter Analytics for Magento 2 (System)
 */

namespace Amasty\ShopbyFilterAnalytics\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Analytics extends AbstractDb
{
    /**
     * @var UnionModelFactory
     */
    private $modelFactory;

    public function __construct(
        Context $context,
        UnionModelFactory $modelFactory
    ) {
        parent::__construct($context);
        $this->modelFactory = $modelFactory;
    }

    public function createUnion(): UnionModel
    {
        return $this->modelFactory->create();
    }

    public function getIdFieldName(): string
    {
        return 'attribute_id';
    }

    /**
     * Emulate Resource Model
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
     */
    protected function _construct()
    {
    }
}
