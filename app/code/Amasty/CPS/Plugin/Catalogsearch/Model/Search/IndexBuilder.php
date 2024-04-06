<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Plugin\Catalogsearch\Model\Search;

use Amasty\CPS\Api\Data\BrandProductInterface;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Filter\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\App\ResourceConnection;

class IndexBuilder
{
    public const FILTER_NAME = 'ambrand_id_filter';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ResourceConnection $resource
    ) {
        $this->storeManager = $storeManager;
        $this->resource = $resource;
    }

    /**
     * @param $subject
     * @param RequestInterface $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function beforeBuild($subject, RequestInterface $request)
    {
        $this->request = $request;
        return [$request];
    }

    /**
     * @param $subject
     * @param \Zend_Db_Select $result
     * @return \Zend_Db_Select
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function afterBuild($subject, $result)
    {
        $filters = $this->getFilters($this->request->getQuery());
        foreach ($filters as $filter) {
            if ($filter->getName() === self::FILTER_NAME) {
                $alias = $filter->getName();
                $result->joinLeft(
                    [$alias => $this->resource->getTableName(BrandProductInterface::MAIN_TABLE)],
                    sprintf(
                        "`{$alias}`.`product_id`=`search_index`.entity_id"
                        . " AND `{$alias}`.`store_id`  =  %d",
                        $this->storeManager->getStore()->getId()
                    ),
                    []
                );
            }
        }

        return $result;
    }

    /**
     * @param RequestQueryInterface $query
     * @return FilterInterface[]
     */
    private function getFilters($query)
    {
        $filters = [];
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_BOOL:
                /** @var \Magento\Framework\Search\Request\Query\BoolExpression $query */
                foreach ($query->getMust() as $subQuery) {
                    //phpcs:ignore
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                foreach ($query->getShould() as $subQuery) {
                    //phpcs:ignore
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                foreach ($query->getMustNot() as $subQuery) {
                    //phpcs:ignore
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                break;
            case RequestQueryInterface::TYPE_FILTER:
                /** @var Filter $query */
                $filter = $query->getReference();
                if (FilterInterface::TYPE_BOOL === $filter->getType()) {
                    $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
                } else {
                    $filters[] = $filter;
                }
                break;
            default:
                break;
        }
        return $filters;
    }

    /**
     * @param BoolExpression $boolExpression
     * @return FilterInterface[]
     */
    private function getFiltersFromBoolFilter(BoolExpression $boolExpression)
    {
        $filters = [];
        /** @var BoolExpression $filter */
        foreach ($boolExpression->getMust() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                //phpcs:ignore
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getShould() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                //phpcs:ignore
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getMustNot() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                //phpcs:ignore
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        return $filters;
    }
}
