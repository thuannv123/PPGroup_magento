<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product\Indexer\Price\Dimensional\Configurable;

use Amasty\Shopby\Model\ConfigProvider;
use Amasty\Shopby\Model\ResourceModel\Catalog\Product\Indexer\Price\Configurable as ConfigurableResource;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable;
use Magento\Framework\Search\Request\IndexScopeResolverInterface as TableResolver;

class AddIndexSpecialPrice
{
    public const MAIN_INDEX_TABLE = 'catalog_product_index_price';
    public const TABLE_SUFFIX = '_temp';

    /**
     * @var ConfigurableResource
     */
    private $configurableResource;

    /**
     * @var TableResolver
     */
    private $tableResolver;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigurableResource $configurableResource,
        TableResolver $tableResolver,
        ConfigProvider $configProvider
    ) {
        $this->configurableResource = $configurableResource;
        $this->tableResolver = $tableResolver;
        $this->configProvider = $configProvider;
    }

    /**
     * @param Configurable $subject
     * @param mixed $result
     * @param array $dimensions
     * @param \Traversable $entityIds
     * @return mixed
     */
    public function afterExecuteByDimensions($subject, $result, array $dimensions, \Traversable $entityIds)
    {
        $entityIds = iterator_to_array($entityIds);
        if ($entityIds && $this->isActive()) {
            $this->configurableResource->addSpecialPrice(
                $this->getDataTable($dimensions),
                $this->getIdxTable($dimensions),
                $entityIds
            );
        }

        return $result;
    }

    private function getDataTable(array $dimensions): string
    {
        return $this->tableResolver->resolve(self::MAIN_INDEX_TABLE, $dimensions);
    }

    private function getIdxTable(array $dimensions): string
    {
        return $this->tableResolver->resolve(self::MAIN_INDEX_TABLE, $dimensions) . self::TABLE_SUFFIX;
    }

    /**
     * Check if additional indexation needed.
     * Method used by Amasty_VisualMerch.
     */
    public function isActive(): bool
    {
        return $this->configProvider->isSaleFilterEnabled();
    }
}
