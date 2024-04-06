<?php

namespace PPGroup\Checkout\Plugin;

use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Checkout\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Ui\Component\Form\AttributeMapper;
use Magento\Customer\Model\AttributeMetadataDataProvider;

class CustomerCustomAttributesLayoutProcessorPlugin
{
    /**
     * @var Data
     */
    private $checkoutDataHelper;

    /**
     * @var AttributeMetadataDataProvider
     */
    protected $attributeMetadataDataProvider;

    /**
     * @var AttributeMapper
     */
    protected $attributeMapper;

    /**
     * @var AttributeMerger
     */
    protected $merger;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AttributeMapper $attributeMapper,
        AttributeMerger $merger,
        ScopeConfigInterface $scopeConfig = null,
        Data $checkoutDataHelper = null
    )
    {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
        $this->merger = $merger;
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->checkoutDataHelper = $checkoutDataHelper ?: ObjectManager::getInstance()->get(Data::class);
    }

    public function afterProcess(
        \Magento\CustomerCustomAttributes\Block\Checkout\LayoutProcessor $subject,
        array $result
    )
    {
        $enableAddressSearchConfig = (bool)$this->scopeConfig->getValue(
            'checkout/options/enable_address_search',
            ScopeInterface::SCOPE_STORE
        );

        // do not proceed if billing address is managed with ui-select
        if ($enableAddressSearchConfig) {
            return $result;
        }

        if (!$this->checkoutDataHelper->isDisplayBillingOnPaymentMethodAvailable()) {
            $addressCustomAttributes = $this->getAddressCustomAttributes();
            $result = $this->processCustomAttributesForAfterPaymentMethods($result, $addressCustomAttributes);
        }

        return $result;
    }

    /**
     * Returns a list of custom attributes for customer addresses.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAddressCustomAttributes()
    {
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer_address',
            'customer_register_address'
        );
        $addressCustomAttributes = [];
        foreach ($attributes as $attribute) {
            if (!$attribute->getIsUserDefined()) {
                continue;
            }
            $addressCustomAttributes[$attribute->getAttributeCode()] = $this->attributeMapper->map($attribute);
        }

        return $addressCustomAttributes;
    }

    /**
     * @param array $jsLayout
     * @param array $addressCustomAttributes
     * @return array
     */
    private function processCustomAttributesForAfterPaymentMethods(
        array $jsLayout,
        array $addressCustomAttributes
    )
    {
        $fields = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children'];
        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children'] = $this->merger->merge(
            $addressCustomAttributes,
            'checkoutProvider',
            'billingAddressshared.custom_attributes',
            $fields
        );

        return $jsLayout;
    }
}
