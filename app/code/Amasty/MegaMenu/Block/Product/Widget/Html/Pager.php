<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Mega Menu Base for Magento 2
 */

namespace Amasty\MegaMenu\Block\Product\Widget\Html;

class Pager extends \Magento\Catalog\Block\Product\Widget\Html\Pager
{
    /**
     * @param array $params
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        $urlParams = [];
        $urlParams['_escape'] = true;
        $urlParams['_query'] = $params;

        return $this->getUrl($this->getPath(), $urlParams);
    }

    /**
     * Get path
     *
     * @return string
     */
    protected function getPath()
    {
        return $this->_getData('path') ?: 'ammegamenu/pager/change';
    }
}
