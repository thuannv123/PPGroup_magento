<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PPGroup\ZipcodeSuggestion\Model\Address\Validator;

use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Address\ValidatorInterface;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Escaper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\ValidateException;
use Magento\Framework\Validator\ValidatorChain;
use Magento\Store\Model\ScopeInterface;

/**
 * Address country and region validator.
 */
class Country extends \Magento\Customer\Model\Address\Validator\Country
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Data
     */
    private $directoryData;

    /**
     * @var AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * @param Data $directoryData
     * @param AllowedCountries $allowedCountriesReader
     * @param Escaper|null $escaper
     */
    public function __construct(
        Data $directoryData,
        AllowedCountries $allowedCountriesReader,
        Escaper $escaper = null
    ) {
        parent::__construct(
            $directoryData,
            $allowedCountriesReader,
            $escaper
        );
        $this->allowedCountriesReader = $allowedCountriesReader;
        $this->directoryData = $directoryData;
        $this->escaper = $escaper;
    }

    /**
     * @inheritdoc
     */
    public function validate(AbstractAddress $address)
    {
        $errors = $this->validateCountry($address);
        if (empty($errors)) {
            $errors = $this->validateRegion($address);
        }

        return $errors;
    }

    /**
     * Validate country existence.
     *
     * @param AbstractAddress $address
     * @return array
     */
    public function validateCountry(AbstractAddress $address)
    {
        $countryId = $address->getCountryId();
        $errors = [];
        if (!ValidatorChain::is($countryId, NotEmpty::class)) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'countryId']);
        } elseif (!in_array($countryId, $this->getWebsiteAllowedCountries($address), true)) {
            //Checking if such country exists.
            $errors[] = __(
                'Please correct your zip code and address.'
            );
        }

        return $errors;
    }

    /**
     * Validate region existence.
     *
     * @param AbstractAddress $address
     * @return array
     * @throws ValidateException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateRegion(AbstractAddress $address)
    {
        $errors = [];
        $countryId = $address->getCountryId();
        $countryModel = $address->getCountryModel();
        $regionCollection = $countryModel->getRegionCollection();
        $region = $address->getRegion();
        $regionId = (string)$address->getRegionId();
        $allowedRegions = $regionCollection->getAllIds();
        $isRegionRequired = $this->directoryData->isRegionRequired($countryId);
        if ($isRegionRequired && empty($allowedRegions) && !ValidatorChain::is($region, NotEmpty::class)) {
            //If region is required for country and country doesn't provide regions list
            //region must be provided.
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'region']);
        } elseif ($allowedRegions && !ValidatorChain::is($regionId, NotEmpty::class) && $isRegionRequired) {
            //If country actually has regions and requires you to
            //select one then it must be selected.
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'regionId']);
        } elseif ($allowedRegions && $regionId && !in_array($regionId, $allowedRegions, true)) {
            //If a region is selected then checking if it exists.
            $errors[] = __(
                'Please correct your zip code and address.'
            );
        }

        return $errors;
    }

    public function getWebsiteAllowedCountries(AbstractAddress $address): array
    {
        $storeId = $address->getData('store_id');
        return $this->allowedCountriesReader->getAllowedCountries(ScopeInterface::SCOPE_STORE, $storeId);
    }
}
