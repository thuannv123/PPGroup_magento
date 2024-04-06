<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Infinite Scroll for Magento 2
 */

namespace Amasty\Scroll\Plugin;

use \Magento\Theme\Block\Html\Pager as NativePager;

class Pager
{
    /**
     * @param NativePager $subject
     * @param $result
     *
     * @return string
     */
    public function afterToHtml(
        NativePager $subject,
        $result
    ) {
        $last = (int)$subject->getLastPageNum();
        $result .= '<div id="am-page-count" style="display: none">' . $last . '</div>';

        return $result;
    }
}
