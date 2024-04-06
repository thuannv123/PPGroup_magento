<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Grouped Options for Magento 2
 */

namespace Amasty\GroupedOptions\Model\ResourceModel\Product\Attribute;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

class LoadUsedForGroups
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param array|null $filterIds
     * @return Attribute[]
     */
    public function execute(?array $filterIds): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addIsFilterableFilter();
        $collection->addFieldToFilter('frontend_input', ['neq' => 'boolean']);
        if ($filterIds !== null) {
            $collection->addFieldToFilter('main_table.attribute_id', ['in' => $filterIds]);
        }

        return $collection->getItems();
    }
}
