<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation by Amasty - Filter Analytics for Magento 2 (System)
 */

namespace Amasty\ShopbyFilterAnalytics\Block\System;

use Amasty\ShopbyFilterAnalytics\Model\FunctionalityManager;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class OptionsLimit extends Field
{
    /**
     * @var FunctionalityManager
     */
    private $functionalityManager;

    public function __construct(
        Context $context,
        FunctionalityManager $functionalityManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->functionalityManager = $functionalityManager;
    }

    public function render(AbstractElement $element)
    {
        if (!$this->functionalityManager->isPremActive()) {
            return '';
        }

        return parent::render($element);
    }
}
