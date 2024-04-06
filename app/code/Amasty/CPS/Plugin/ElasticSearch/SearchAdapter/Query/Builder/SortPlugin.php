<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Plugin\ElasticSearch\SearchAdapter\Query\Builder;

use Amasty\ShopbyBrand\Model\BrandResolver;

class SortPlugin
{
    public const FIELD_NAME_POSITION_TEMPLATE = 'brand_position_%s';

    /**
     * @var BrandResolver
     */
    private $brandResolver;

    public function __construct(
        BrandResolver $brandResolver
    ) {
        $this->brandResolver = $brandResolver;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return array
     */
    public function afterGetSort($subject, $result)
    {
        if ($brand = $this->brandResolver->getCurrentBrand()) {
            foreach ($result as $sortKey => $sort) {
                $key = key($sort);
                $order = $sort[$key]['order'];
                if (strpos($key, 'category_position') === 0 || strpos($key, 'position_category') === 0) {
                     $result[$sortKey] = [
                        sprintf(self::FIELD_NAME_POSITION_TEMPLATE, $brand->getValue()) => [
                            'order' => strtolower($order)
                        ]
                     ];
                }
            }
        }

        return $result;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return array
     */
    public function afterExecute($subject, $result)
    {
        return $this->afterGetSort($subject, $result);
    }
}
