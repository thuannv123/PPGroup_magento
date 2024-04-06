<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Content\Lists;

use Amasty\Blog\Controller\AbstractController\Search;
use Amasty\Blog\Model\UrlResolver;

class Pager extends \Magento\Theme\Block\Html\Pager
{
    /**
     * @var \Amasty\Blog\Helper\Settings
     */
    private $settings;

    /**
     * @var bool
     */
    private $isSearch = false;

    /**
     * @var bool
     */
    private $isMultiSearch = false;

    /**
     * @var null
     */
    private $object = null;

    /**
     * @var null
     */
    private $query = null;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Blog\Helper\Settings $settings,
        UrlResolver $urlResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->settings = $settings;
        $this->urlResolver = $urlResolver;
    }

    /**
     * @param $object
     * @return $this
     */
    public function setPagerObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return null
     */
    public function getPagerObject()
    {
        return $this->object;
    }

    /**
     * @param bool $isSearch
     * @return $this
     */
    public function setSearchPage($isSearch)
    {
        $this->isSearch = $isSearch;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSearchPage()
    {
        return $this->isSearch;
    }

    /**
     * @param string $page
     * @return string
     */
    public function getPageUrl($page)
    {
        if ($this->getIsMultiSearch()) {
            return $this->getPageUrlFromMultisearch($page);
        }

        $url = $this->isSearchPage()
            ? $this->urlResolver->getSearchPageUrl($page)
            : $this->getPagerObject()->getUrl($page);

        $delimiter = '';
        if ($this->getQuery()) {
            $delimiter = strpos($url, '?') === false ? '?' : '&';
        }

        return $url . $delimiter . $this->getQuery();
    }

    public function getPageUrlFromMultisearch(int $page)
    {
        $searchParam = Search::SEARCH_PARAM;
        $query = $this->getQuery() ?? $this->getRequest()->getParam($searchParam);

        if ($page === 1) {
            return sprintf('%s?%s=%s', $this->urlResolver->getSearchPageUrl($page), $searchParam, $query);
        }

        $url = $this->urlResolver->getSearchPageUrlWithoutPostfix();

        return sprintf(
            '%s?%s=%s&%s=%d',
            $url,
            $searchParam,
            $query,
            $this->getPageVarName(),
            $page
        );
    }

    /**
     * @return bool
     */
    public function isOldStyle()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getColorClass()
    {
        return $this->settings->getIconColorClass();
    }

    /**
     * Get Url Postfix
     *
     * @return null
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set URL postfix
     *
     * @param $query
     *
     * @return $this
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    public function setIsMultiSearch(bool $isMultiSearch): self
    {
        $this->isMultiSearch = $isMultiSearch;

        return $this;
    }

    public function getIsMultiSearch(): bool
    {
        return $this->isMultiSearch;
    }

    /**
     * Return current page
     *
     * @return int
     */
    public function getCurrentPage()
    {
        if (is_object($this->_collection)) {
            return $this->_collection->getCurPage();
        }

        $pageNum = (int)$this->getRequest()->getParam($this->getPageVarName());

        return $pageNum ?: 1;
    }
}
