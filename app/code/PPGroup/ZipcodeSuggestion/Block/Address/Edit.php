<?php

namespace PPGroup\ZipcodeSuggestion\Block\Address;

use Magento\Customer\Api\AddressMetadataInterface;

class Edit extends \Magento\Customer\Block\Address\Edit
{

    protected $zipcodeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        AddressMetadataInterface $addressMetadata = null,
        \PPGroup\ZipcodeSuggestion\Model\Config $zipcodeConfig,
        array $data = []
    ) {
        $this->zipcodeConfig = $zipcodeConfig;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $customerSession,
            $addressRepository,
            $addressDataFactory,
            $currentCustomer,
            $dataObjectHelper,
            $data,
            $addressMetadata
        );
    }

    /**
     * GetBackendEnable
     *
     * @return bool|mixed
     */
    public function getBackendEnable()
    {
        return $this->zipcodeConfig->getModuleStatusInBackend();
    }
}
