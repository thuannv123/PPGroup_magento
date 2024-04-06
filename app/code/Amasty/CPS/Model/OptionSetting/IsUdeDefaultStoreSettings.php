<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Model\OptionSetting;

use Amasty\CPS\Api\Data\BrandProductInterface;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Magento\Store\Model\Store;

class IsUdeDefaultStoreSettings
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    public function execute(int $currentStoreId, OptionSettingInterface $option): bool
    {
        return !(int)$option->getData(BrandProductInterface::BRAND_USE_DEFAULT_STORE_SETTING)
            && $this->configProvider->getBrandAttributeCode($currentStoreId)
            === $this->configProvider->getBrandAttributeCode(Store::DEFAULT_STORE_ID);
    }
}
