<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model\Brand;

use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;
use Amasty\ShopbyBase\Helper\OptionSetting as OptionSettingHelper;
use Amasty\ShopbyBase\Model\OptionSetting;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Framework\Exception\NoSuchEntityException;

class OptionsUpdater
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var OptionSettingHelper
     */
    private $optionSettingHelper;

    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var OptionSettingRepositoryInterface
     */
    private $optionSettingRepository;

    public function __construct(
        ConfigProvider $configProvider,
        OptionSettingHelper $optionSettingHelper,
        AttributeRepository $attributeRepository,
        OptionSettingRepositoryInterface $optionSettingRepository
    ) {
        $this->configProvider = $configProvider;
        $this->optionSettingHelper = $optionSettingHelper;
        $this->attributeRepository = $attributeRepository;
        $this->optionSettingRepository = $optionSettingRepository;
    }

    public function execute(?string $attributeCode = null)
    {
        foreach (array_unique($this->configProvider->getAllBrandAttributeCodes()) as $attrCode) {
            if (!$attrCode || ($attributeCode && $attrCode !== $attributeCode)) {
                continue;
            }

            $currentAttributeValues = $this->getCurrentBrandAttributeValues($attrCode);
            $this->addMissingBrandOptions($currentAttributeValues, $attrCode);
        }
    }

    /**
     * @param string $attrCode
     * @return string[]
     */
    private function getCurrentBrandAttributeValues(string $attrCode): array
    {
        $attributeValues = [];
        try {
            /** @var \Magento\Eav\Model\Entity\Attribute\Option[]  $attributeOptions */
            $attributeOptions = $this->attributeRepository->get($attrCode)->getOptions();
        } catch (NoSuchEntityException $exception) {
            return  $attributeValues;
        }

        foreach ($attributeOptions as $option) {
            if ($option->getValue()) {
                $attributeValues[] = $option->getValue();
            }
        }

        return $attributeValues;
    }

    private function addMissingBrandOptions(array $currentAttributeValues, string $attributeCode): void
    {
        foreach ($currentAttributeValues as $value) {
            /** @var OptionSetting $optionSetting */
            $optionSetting = $this->optionSettingHelper->getSettingByOption((int)$value, $attributeCode, 0);
            if (!$optionSetting->getId()) {
                try {
                    $this->optionSettingRepository->save($optionSetting);
                } catch (\Exception $ex) {
                    continue;
                }
            }
        }
    }
}
