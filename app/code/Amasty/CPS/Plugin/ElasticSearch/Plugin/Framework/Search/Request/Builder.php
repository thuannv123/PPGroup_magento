<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Plugin\ElasticSearch\Plugin\Framework\Search\Request;

use Amasty\ElasticSearch\Model\Config;
use Amasty\ShopbyBrand\Model\BrandResolver;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogInventory\Model\Stock;

class Builder
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var BrandResolver
     */
    private $brandResolver;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        BrandResolver $brandResolver
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->brandResolver = $brandResolver;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     * @param $argument
     * @return array
     */
    public function aroundBeforeCreate($subject, \Closure $proceed, $argument)
    {
        if ($this->brandResolver->getCurrentBrand()
            && $this->scopeConfig->getValue(EngineProvider::CONFIG_ENGINE_PATH) == Config::ELASTIC_SEARCH_ENGINE
            && !$this->scopeConfig->isSetFlag('cataloginventory/options/show_out_of_stock')
        ) {
            $argument->bind('stock_status', Stock::STOCK_IN_STOCK);
        }

        return [];
    }
}
