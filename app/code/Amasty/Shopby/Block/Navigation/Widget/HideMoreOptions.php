<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Block\Navigation\Widget;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;

class HideMoreOptions extends \Magento\Framework\View\Element\Template implements WidgetInterface
{
    /**
     * @var FilterSettingInterface
     */
    private $filterSetting;

    /**
     * @var string
     */
    protected $_template = 'Amasty_Shopby::layer/widget/hide_more_options.phtml';

    /**
     * @param FilterSettingInterface $filterSetting
     * @return $this
     */
    public function setFilterSetting(FilterSettingInterface $filterSetting)
    {
        $this->filterSetting = $filterSetting;
        return $this;
    }

    /**
     * @return FilterSettingInterface
     */
    public function getFilterSetting()
    {
        return $this->filterSetting;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        return ($this->getIsState() && $this->getUnfoldedOptions()) || $this->filterSetting->getNumberUnfoldedOptions()
            ? parent::toHtml()
            : '';
    }
}
