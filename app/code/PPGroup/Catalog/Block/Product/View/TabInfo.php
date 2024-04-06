<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PPGroup\Catalog\Block\Product\View;

use Magento\Catalog\Model\Product;

/**
 * @api
 * @since 100.0.2
 */
class TabInfo extends \Magento\Framework\View\Element\Template
{
    const ATTRIBUTE_SHORT_DESCRIPTION = 'short_description';
    const ATTRIBUTE_DESCRIPTION = 'description';

    /**
     * @var Product
     */
    protected $_product = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }

    /**
     * @param $_helper
     * @return mixed
     */
    public function getShortDescription($_helper)
    {
        return $_helper->productAttribute($this->getProduct(), $this->getProduct()->getShortDescription(), self::ATTRIBUTE_SHORT_DESCRIPTION);
    }

    /**
     * @param $_helper
     * @return mixed
     */
    public function getDescription($_helper)
    {
        return $_helper->productAttribute($this->getProduct(), $this->getProduct()->getDescription(), self::ATTRIBUTE_DESCRIPTION);
    }

    /**
     * @param $_helper
     * @return bool
     */
    public function canShowTab($_helper): bool
    {
        if($this->canShowDescription($_helper) || $this->canShowShortDescription($_helper)) {
            return true;
        }
        return false;
    }

    /**
     * @param $_helper
     * @return bool
     */
    public function canShowDescription($_helper): bool
    {
        if(!is_null($this->getDescription($_helper))) {
            return true;
        }
        return false;
    }

    /**
     * @param $_helper
     * @return bool
     */
    public function canShowShortDescription($_helper): bool
    {
        if(!is_null($this->getShortDescription($_helper))) {
            return true;
        }
        return false;
    }
}
