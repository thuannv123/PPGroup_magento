<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class BrandManagementLink extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _renderValue(AbstractElement $element): string
    {
        $url = $this->getUrl('amasty_shopbybrand/slider/index');
        $message = __('Please click here to configure Custom Product Sorting on Brand Pages');

        return sprintf("<a class='cps-brands-link' href='%s' target='_blank'>%s</a>", $url, $message);
    }
}
