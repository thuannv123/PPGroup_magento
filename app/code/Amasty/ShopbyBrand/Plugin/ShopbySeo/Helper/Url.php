<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\ShopbySeo\Helper;

use Amasty\ShopbyBrand\Helper\Data as BrandHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Url
{
    /**
     * @var BrandHelper
     */
    private $brandHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        BrandHelper $brandHelper,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->brandHelper = $brandHelper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $subject
     * @param $identifier
     * @return array
     */
    public function beforeModifySeoIdentifier($subject, $identifier)
    {
        $allProductsIdentifier = $this->scopeConfig->getValue(
            'amshopby_root/general/url',
            ScopeInterface::SCOPE_STORE
        );
        $brandUrlKey = $this->scopeConfig->getValue(
            BrandHelper::PATH_BRAND_URL_KEY,
            ScopeInterface::SCOPE_STORE
        );
        $trimmedIdentifier = trim($identifier, DIRECTORY_SEPARATOR);
        if ($trimmedIdentifier
            && $subject->getParam($this->brandHelper->getBrandAttributeCode())
            && (in_array($trimmedIdentifier, [$allProductsIdentifier, $brandUrlKey])
                || in_array($trimmedIdentifier, $this->brandHelper->getBrandAliases()))
        ) {
            $brandId = $subject->getParam($this->brandHelper->getBrandAttributeCode());
            if (is_array($brandId)) {
                $brandId = current($brandId);
            }
            $aliases = $this->brandHelper->getBrandAliases();
            if (isset($aliases[$brandId])) {
                $subject->setParam($this->brandHelper->getBrandAttributeCode(), null);
                $brandAlias = $aliases[$brandId];
                $identifier = !!$brandUrlKey ? $brandUrlKey . '/' . $brandAlias : $brandAlias;
            }
        }
        return [$identifier];
    }
}
