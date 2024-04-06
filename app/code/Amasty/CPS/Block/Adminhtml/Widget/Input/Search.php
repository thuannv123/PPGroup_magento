<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Block\Adminhtml\Widget\Input;

class Search extends \Magento\Backend\Block\Widget
{
    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Amasty_VisualMerchUi::widget/input.phtml');
    }
}
