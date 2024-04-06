<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation by Amasty - Filter Analytics for Magento 2 (System)
 */

namespace Amasty\ShopbyFilterAnalytics\Model\SkipFilter;

use Amasty\Shopby\Model\Layer\IsBrandPage;
use Amasty\ShopbyBrand\Model\ConfigProvider as BrandConfigProvider;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

class Brand implements FilterToSkipInterface
{
    /**
     * @var BrandConfigProvider
     */
    private $brandConfigProvider;

    /**
     * @var IsBrandPage
     */
    private $isBrandPage;

    public function __construct(
        BrandConfigProvider $brandConfigProvider,
        IsBrandPage $isBrandPage
    ) {
        $this->brandConfigProvider = $brandConfigProvider;
        $this->isBrandPage = $isBrandPage;
    }

    public function execute(AbstractFilter $filter): bool
    {
        return $this->brandConfigProvider->getBrandAttributeCode() === $filter->getRequestVar()
            && $this->isBrandPage->execute();
    }
}
