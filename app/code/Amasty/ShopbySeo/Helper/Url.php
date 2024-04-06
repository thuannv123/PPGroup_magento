<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Helper;

use Amasty\ShopbyBase\Helper\Data as BaseHelper;
use Amasty\ShopbySeo\Helper\Url as UrlHelper;
use Amasty\ShopbySeo\Model\SeoOptions;
use Amasty\ShopbySeo\Model\UrlRewrite\IsExist as IsUrlRewriteExist;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\ScopeInterface;

class Url extends AbstractHelper
{
    public const CATALOG_MODULE_NAME = 'catalog';

    public const CATEGORY_FILTER_PARAM = 'cat';

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var string
     */
    private $paramsDelimiter;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var bool|null
     */
    private $isAddSuffixToShopby = null;

    /**
     * @var int[]
     */
    private $filterPositions;

    /**
     * @var UrlParser
     */
    private $urlParser;

    /**
     * @var array
     */
    private $allowedModules = [
        'catalog',
        'ambrand',
        'amshopby'
    ];

    /**
     * @var array
     */
    private $disallowedControllers = [
        'product_compare'
    ];

    /**
     * @var array
     */
    private $disallowedPathes = [
        'media/'
    ];

    /**
     * @var array
     */
    private $queryParams = [];

    /**
     * @var string
     */
    private $identifier = '';

    /**
     * @var bool
     */
    private $hasSeoAliases = false;

    /**
     * @var string
     */
    private $originalIdentifier;

    /**
     * @var bool
     */
    private $hasUrlSuffix = false;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var SeoOptions
     */
    private $seoOptions;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var IsUrlRewriteExist|null
     */
    private $isUrlRewriteExist;

    public function __construct(
        Context $context,
        Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\ShopbySeo\Helper\UrlParser $urlParser,
        \Amasty\ShopbySeo\Helper\Config $config,
        DataPersistorInterface $dataPersistor,
        SeoOptions $seoOptions,
        ?CategoryRepositoryInterface $categoryRepository, // TODO: remove
        IsUrlRewriteExist $isUrlRewriteExist = null // TODO move to not optional
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->urlParser = $urlParser;
        $this->config = $config;
        $this->dataPersistor = $dataPersistor;
        $this->seoOptions = $seoOptions;
        $this->categoryRepository = $categoryRepository;
        $this->isUrlRewriteExist = $isUrlRewriteExist ?? ObjectManager::getInstance()->get(IsUrlRewriteExist::class);
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_getRequest();
    }

    /**
     * @param string $url
     * @param int|null $categoryId
     * @param bool $skipModuleCheck
     * @return string
     */
    public function seofyUrl($url, ?int $categoryId = null, bool $skipModuleCheck = false)
    {
        if (!$this->initialize($url, $skipModuleCheck)) {
            return $url;
        }

        $this->hasUrlSuffix = false;

        $identifier = $this->removeCategorySuffix($this->identifier);
        if ($this->identifier !== $identifier) {
            $this->hasUrlSuffix = true;
        }

        $isCategoryPathRemoved = false;
        if ($categoryPath = $this->retrieveCategoryUrl($url, $categoryId)) {
            $identifierWithoutCategoryPath = $this->removeCategoryPath($identifier, $categoryPath);
            $isCategoryPathRemoved = $identifier !== $identifierWithoutCategoryPath;
            $identifier = $identifierWithoutCategoryPath;
        }

        if ($this->isSeoUrlEnabled()) {
            $identifier = $this->injectAliases($identifier);
            $identifier = ltrim($identifier, DIRECTORY_SEPARATOR);
        }

        if ($isCategoryPathRemoved) {
            $identifier = $this->addCategoryPath($identifier, $categoryPath);
        }
        if ($this->hasUrlSuffix || $this->getRequest()->getMetaData(Data::SEO_REDIRECT_MISSED_SUFFIX_FLAG)) {
            $identifier = $this->addCategorySuffix($identifier);
        }

        if ($this->identifier !== $identifier) {
            if ($this->hasParams()) {
                $identifier .= '?' . $this->buildQuery();
            }
            $url = str_replace(trim($this->originalIdentifier, '/'), $identifier, $url);
        }

        return $url;
    }

    private function removeCategoryPath(string $identifier, string $categoryPath): string
    {
        if (substr($identifier, 0, strlen($categoryPath)) === $categoryPath) {
            return substr($identifier, strlen($categoryPath));
        }
        return $identifier;
    }

    private function addCategoryPath(string $identifier, string $categoryPath): string
    {
        if ($identifier) {
            return $categoryPath . '/' . $identifier;
        }
        return $categoryPath;
    }

    private function retrieveCategoryUrl(string $url, ?int $categoryId): ?string
    {
        if ($categoryId === null && $this->coreRegistry->registry('current_category')) {
            $categoryId = $this->coreRegistry->registry('current_category')->getId();
        }

        if ($categoryId && $this->storeManager->getStore()->getRootCategoryId() != $categoryId) {
            $categoryPath = str_replace($this->getBaseUrl(), '', $this->removeQueryParams($url));
            if ($this->isUrlRewriteExist->execute(
                $categoryPath,
                (int)$this->storeManager->getStore()->getId(),
                CategoryUrlRewriteGenerator::ENTITY_TYPE,
                $categoryId
            )) {
                return $this->removeCategorySuffix($categoryPath);
            }
        }

        return null;
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    public function modifySeoIdentifier($identifier)
    {
        return $identifier;
    }

    /**
     * @param string $identifier
     * @param array $aliases
     *
     * @return string
     */
    public function modifySeoIdentifierByAlias($identifier, $aliases = [])
    {
        return $identifier;
    }

    /**
     * @return bool
     */
    private function isCatalog()
    {
        return !$this->dataPersistor->get(BaseHelper::SHOPBY_BRAND_POPUP)
            && (!$this->getRequest()->getModuleName() == self::CATALOG_MODULE_NAME
                || $this->hasCategoryFilterParam());
    }

    /**
     * @return bool
     */
    public function hasCategoryFilterParam()
    {
        return (bool)$this->getParam(self::CATEGORY_FILTER_PARAM);
    }

    /**
     * @param string $url
     * @param bool $skipModuleCheck
     * @return bool
     */
    private function initialize($url, bool $skipModuleCheck)
    {
        if (!$skipModuleCheck && !in_array($this->getRequest()->getModuleName(), $this->allowedModules)) {
            return false;
        }

        // @codingStandardsIgnoreLine
        $parsedUrl = parse_url($url);

        $url = $this->removeQueryParams($url);

        $this->identifier = substr($url, strlen($this->getBaseUrl()));
        $this->hasSeoAliases = false;
        $this->queryParams = [];

        foreach ($this->disallowedPathes as $path) {
            if (strpos($this->identifier, $path) !== false) {
                return false;
            }
        }

        if (isset($parsedUrl['query'])) {
            $this->paramsDelimiter = strpos($parsedUrl['query'], '&amp;') !== false ? '&amp;' : '&';
            $this->parseQuery($parsedUrl['query']);
        }

        $this->originalIdentifier = $this->identifier . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');

        return true;
    }

    private function removeQueryParams(string $url): string
    {
        $queryPosition = strpos($url, '?');
        if ($queryPosition) {
            return substr($url, 0, $queryPosition);
        }
        return $url;
    }

    private function getBaseUrl(): string
    {
        $switchedStore = $this->dataPersistor->get(BaseHelper::SHOPBY_SWITCHER_STORE_ID);
        $store = $switchedStore ? $this->storeManager->getStore($switchedStore) : $this->storeManager->getStore();
        return $store->getBaseUrl();
    }

    /**
     * @param string $query
     *
     * @return $this
     */
    public function parseQuery($query)
    {
        $queryParams = str_replace($this->paramsDelimiter, '&', $query);
        // @codingStandardsIgnoreLine
        parse_str($queryParams, $this->queryParams);

        return $this;
    }

    /**
     * @return array
     */
    private function getAllAliases()
    {
        $aliases = [];
        $attributeOptionsData = $this->seoOptions->getData();
        foreach ($this->getParams() as $paramName => $rawValues) {
            if ($this->helper->isAttributeSeoSignificant($paramName)
                && isset($attributeOptionsData[$paramName])
            ) {
                $optionsData = $attributeOptionsData[$paramName];
                if (is_array($rawValues)) {
                    foreach ($rawValues as $value) {
                        if (!array_key_exists($value, $optionsData)) {
                            continue;
                        }
                        $aliases[$paramName][] = $optionsData[$value];
                    }
                } elseif (array_key_exists($rawValues, $optionsData)) {
                    $aliases[$paramName][] = $optionsData[$rawValues];
                }
                $this->setParam($paramName, null);
            }
        }

        $this->sortAliases($aliases);

        return $aliases;
    }

    /**
     * @param $parsedParams
     *
     * @return $this
     */
    private function prepareParams($parsedParams)
    {
        $this->queryParams = array_merge_recursive($this->getParams(), $parsedParams);
        foreach ($this->queryParams as $paramName => $rawValues) {
            if (is_array($rawValues)) {
                if (is_array(current($rawValues))) {
                    continue;
                }
                $rawValues = implode(',', $rawValues);
            }
            $rawValues = array_unique(explode(',', str_replace('%2C', ',', $rawValues)));
            $this->setParam($paramName, $rawValues);
        }

        return $this;
    }

    /**
     * @param array $seoAliases
     */
    private function sortAliases(&$seoAliases)
    {
        $filterPositions = $this->getFilterPositions();
        if ($filterPositions) {
            uksort(
                $seoAliases,
                function ($first, $second) use ($filterPositions) {
                    if ($first == $second) {
                        return 0;
                    }

                    if (!isset($filterPositions[$first])) {
                        return 1;
                    }

                    if (!isset($filterPositions[$second])) {
                        return -1;
                    }

                    return $filterPositions[$first] - $filterPositions[$second];
                }
            );
        }
    }

    /**
     * @return int[]|null
     */
    private function getFilterPositions()
    {
        if ($this->filterPositions === null) {
            $allFilters = $this->coreRegistry->registry(\Amasty\Shopby\Model\Layer\FilterList::ALL_FILTERS_KEY);

            if (!$allFilters) {
                return null;
            }

            $this->filterPositions = [];
            $position = 0;

            foreach ($allFilters as $filter) {
                $code = $filter->getRequestVar();
                $this->filterPositions[$code] = $position;
                $position++;
            }
        }

        return $this->filterPositions;
    }

    /**
     * @param $routeUrl
     *
     * @return string
     */
    private function injectAliases($routeUrl)
    {
        if ($this->helper->getFilterWord()) {
            if (strpos($routeUrl, '/' . $this->helper->getFilterWord() . '/') !== false) {
                $filterWordPosition = strpos($routeUrl, '/' . $this->helper->getFilterWord() . '/');
                $seoPart = substr(
                    $routeUrl,
                    $filterWordPosition + strlen('/' . $this->helper->getFilterWord() . '/')
                );
                $routeUrl = substr($routeUrl, 0, $filterWordPosition);
            } else {
                $seoPart = '';
            }
            $parsedParams = $this->urlParser->parseSeoPart($seoPart);
        } else {
            $trimmedRouteUrl = trim($routeUrl, '/');
            if ($lastSlashPosition = strrpos($trimmedRouteUrl, "/")) {
                $seoPart = substr($trimmedRouteUrl, $lastSlashPosition + 1);
                $parsedParams = $this->urlParser->parseSeoPart($seoPart);
                if ($parsedParams) {
                    $routeUrl = substr($trimmedRouteUrl, 0, $lastSlashPosition + 1);
                }
            } else {
                $parsedParams = $this->urlParser->parseSeoPart($trimmedRouteUrl);
                if ($parsedParams) {
                    $routeUrl = '';
                } else {
                    $routeUrl = $trimmedRouteUrl;
                }
            }
        }

        $this->prepareParams($parsedParams);
        $routeUrl = $this->modifySeoIdentifier($routeUrl);

        $allAliases = $this->getAllAliases();

        if ($allAliases) {
            $this->hasSeoAliases = true;
            $routeUrl = rtrim($this->modifySeoIdentifierByAlias($routeUrl, $allAliases), '/') . DIRECTORY_SEPARATOR;
            if ($this->helper->getFilterWord()) {
                $routeUrl .= $this->helper->getFilterWord() . DIRECTORY_SEPARATOR;
            }
            $optionSeparator = $this->config->getOptionSeparator();
            $isWithAttributeName = $this->helper->isIncludeAttributeName();
            $aliasString = '';

            foreach ($allAliases as $code => $alias) {
                if ($aliasString) {
                    $aliasString .= $optionSeparator;
                }
                if ($isWithAttributeName || $code === UrlHelper::CATEGORY_FILTER_PARAM) {
                    $aliasString .= $this->getAttributeUrlAlias($code) . $optionSeparator;
                }
                $aliasString .= implode($optionSeparator, $alias);
            }
            $routeUrl .= $aliasString;
        }

        return $routeUrl;
    }

    private function getAttributeUrlAlias(string $attribute): string
    {
        $attributeUrlAliases = $this->helper->getAttributeUrlAliases();
        $store = $this->storeManager->getStore()->getId();
        $alias = $attributeUrlAliases[$attribute][$store] ?? null;

        return $alias ?: $attribute;
    }

    /**
     * @return string
     */
    private function buildQuery()
    {
        $params = $this->getParams();
        foreach ($params as $name => $value) {
            if (is_array($value)) {
                $params[$name] = implode(',', $value);
            }
        }
        $query = http_build_query($params);

        if ($this->paramsDelimiter) {
            $query = str_replace($this->paramsDelimiter, '&', $query);
            return str_replace('&', $this->paramsDelimiter, $query);
        }

        return $query;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    private function addCategorySuffix($url)
    {
        $suffix = $this->getSeoSuffix();
        if (strlen($suffix)) {
            $url .= $suffix;
        }

        return $url;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function removeCategorySuffix($url)
    {
        $suffix = $this->getSeoSuffix();

        if (strlen($suffix)) {
            $suffixPosition = strrpos($url, $suffix);
            if ($suffixPosition !== false && $suffixPosition == strlen($url) - strlen($suffix)) {
                $url = substr($url, 0, $suffixPosition);
            }
        }

        return $url;
    }

    /**
     * @return bool
     */
    public function isSeoUrlEnabled()
    {
        return $this->config->isSeoUrlEnabled();
    }

    /**
     * @return bool
     */
    public function getAddSuffixSettingValue()
    {
        return $this->scopeConfig->isSetFlag(
            'amasty_shopby_seo/url/add_suffix_shopby',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool|null
     */
    public function isAddSuffixToShopby()
    {
        if ($this->isAddSuffixToShopby === null) {
            $moduleName = $this->getRequest()->getModuleName();
            $controllerName = $this->getRequest()->getControllerName();
            $isModuleAllowed = in_array($moduleName, $this->allowedModules, true);
            $isControllerAllowed = !in_array($controllerName, $this->disallowedControllers, true);
            $isSuffixNotEmpty = (bool)strlen($this->getSeoSuffix());

            if ($isModuleAllowed && $isControllerAllowed && $isSuffixNotEmpty) {
                $this->isAddSuffixToShopby = $this->getAddSuffixSettingValue();
            }
        }

        return $this->isAddSuffixToShopby;
    }

    /**
     * @return string
     */
    public function getSeoSuffix()
    {
        return (string)$this->scopeConfig
            ->getValue('catalog/seo/category_url_suffix', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $paramName
     *
     * @return mixed
     */
    public function getParam($paramName)
    {
        return isset($this->queryParams[$paramName]) ? $this->queryParams[$paramName] : null;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->queryParams;
    }

    /**
     * @return bool
     */
    public function hasParams()
    {
        return !empty($this->queryParams);
    }

    /**
     * @param string $paramName
     * @param null $paramValue
     *
     * @return $this
     */
    public function setParam($paramName, $paramValue = null)
    {
        if ($paramValue === null) {
            unset($this->queryParams[$paramName]);
        } else {
            $this->queryParams[$paramName] = $paramValue;
        }

        return $this;
    }
}
