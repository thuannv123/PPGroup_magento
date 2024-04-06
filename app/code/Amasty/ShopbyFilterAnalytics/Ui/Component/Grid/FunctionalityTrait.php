<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation by Amasty - Filter Analytics for Magento 2 (System)
 */

namespace Amasty\ShopbyFilterAnalytics\Ui\Component\Grid;

use Amasty\ShopbyFilterAnalytics\Model\FunctionalityManager;

trait FunctionalityTrait
{
    /**
     * @var FunctionalityManager
     */
    private $functionalityManager;

    public function getData($key = '', $index = null)
    {
        if ($this->functionalityManager->isPremActive()) {
            return parent::getData($key, $index);
        }

        if ($key === '') {
            return [];
        }

        return null;
    }

    public function render()
    {
        if ($this->functionalityManager->isPremActive()) {
            return parent::render();
        }

        return '';
    }

    public function prepare()
    {
        if ($this->functionalityManager->isPremActive()) {
            parent::prepare();
        }
    }
}
