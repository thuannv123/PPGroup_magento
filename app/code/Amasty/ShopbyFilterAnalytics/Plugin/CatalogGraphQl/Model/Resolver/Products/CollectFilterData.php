<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation by Amasty - Filter Analytics for Magento 2 (System)
 */

namespace Amasty\ShopbyFilterAnalytics\Plugin\CatalogGraphQl\Model\Resolver\Products;

use Amasty\ShopbyFilterAnalytics\Model\CollectGraphQlFilterData;
use Magento\CatalogGraphQl\Model\Resolver\Products;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\Context;

class CollectFilterData
{
    /**
     * @var CollectGraphQlFilterData
     */
    private $collectGraphQlFilterData;

    public function __construct(CollectGraphQlFilterData $collectGraphQlFilterData)
    {
        $this->collectGraphQlFilterData = $collectGraphQlFilterData;
    }

    /**
     * @param Products $products
     * @param Field $field
     * @param Context $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeResolve(
        Products $products,
        Field $field,
        Context $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): void {
        if (isset($args['filter'])) {
            $filters = $args['filter'];
            if (isset($filters['category_id']['eq'])) {
                $category = (int) $filters['category_id']['eq'];
                unset($filters['category_id']);
            }
            if ($filters) {
                $this->collectGraphQlFilterData->execute($filters, $category ?? null);
            }
        }
    }
}
