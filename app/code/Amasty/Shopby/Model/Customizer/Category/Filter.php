<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Customizer\Category;

use Amasty\ShopbyBase\Api\CategoryDataSetterInterface;
use Magento\Catalog\Model\Category;
use Amasty\ShopbyBase\Model\Customizer\Category\CustomizerInterface;

class Filter implements CustomizerInterface
{
    /**
     * @var CustomizerInterface
     */
    protected $_contentHelper;

    /**
     * @param CategoryDataSetterInterface $contentHelper
     */
    public function __construct(CategoryDataSetterInterface $contentHelper)
    {
        $this->_contentHelper = $contentHelper;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function prepareData(Category $category)
    {
        $this->_contentHelper->setCategoryData($category);
        return $this;
    }
}
