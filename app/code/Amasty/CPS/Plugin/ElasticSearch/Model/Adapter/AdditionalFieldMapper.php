<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Plugin\ElasticSearch\Model\Adapter;

use Amasty\ShopbyBrand\Helper\Data;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;

class AdditionalFieldMapper
{
    public const ES_DATA_TYPE_INTEGER = 'integer';
    public const FIELD_NAME_POSITION_TEMPLATE = 'brand_position_%s';
    public const FIELD_NAME = 'ambrand_id';

    /**
     * @var Repository
     */
    private $attributeRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Repository $attributeRepository,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->configProvider = $configProvider;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllAttributesTypes($subject, array $result)
    {
        $result[self::FIELD_NAME] = ['type' => self::ES_DATA_TYPE_INTEGER];
        $addedAttributeCodes = [];

        foreach ($this->storeManager->getStores() as $store) {
            $brandAttributeCode = $this->configProvider->getBrandAttributeCode($store->getId());

            if (!$brandAttributeCode) {
                $this->logger->notice(
                    __('Amasty Custom Product Sorting indexation error: Brand attribute is not set')->render()
                );
            } elseif (!in_array($brandAttributeCode, $addedAttributeCodes)) {
                $options = $this->attributeRepository->get($brandAttributeCode)->getOptions();

                foreach ($options as $option) {
                    $result[sprintf(self::FIELD_NAME_POSITION_TEMPLATE, $option->getValue())] = [
                        'type' => self::ES_DATA_TYPE_INTEGER
                    ];
                }

                $addedAttributeCodes[] = $brandAttributeCode;
            }
        }

        return $result;
    }

    /**
     * Amasty Elastic entity builder plugin
     *
     * @param mixed $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuildEntityFields($subject, array $result)
    {
        return $this->afterGetAllAttributesTypes($subject, $result);
    }
}
