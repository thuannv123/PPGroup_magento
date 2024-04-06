<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation by Amasty - Filter Analytics for Magento 2 (System)
 */

namespace Amasty\ShopbyFilterAnalytics\Ui\Component\Grid;

use Amasty\ShopbyFilterAnalytics\Model\FunctionalityManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class MassAction extends \Magento\Ui\Component\MassAction
{
    use FunctionalityTrait;

    public function __construct(
        ContextInterface $context,
        FunctionalityManager $functionalityManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->functionalityManager = $functionalityManager;
    }
}
