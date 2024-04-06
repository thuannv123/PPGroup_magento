<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Sales\Block\Adminhtml\Order\Create\Form;

use Magento\Backend\Model\Session\Quote;
use Magento\Directory\Model\CountryHandlerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Order create address form
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Address extends \Magento\Sales\Block\Adminhtml\Order\Create\Form\Address
{
    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareForm()
    {
        $fieldset = $this->_form->addFieldset('main', ['no_container' => true]);

        $addressForm = $this->_customerFormFactory->create('customer_address', 'adminhtml_customer_address');
        $attributes = $addressForm->getAttributes();
//        echo "<pre>";print_r($attributes);echo "</pre>";
        $this->_addAttributesToForm($attributes, $fieldset);

        $prefixElement = $this->_form->getElement('prefix');
        if ($prefixElement) {
            $prefixOptions = $this->options->getNamePrefixOptions($this->getStore());
            if (!empty($prefixOptions)) {
                $fieldset->removeField($prefixElement->getId());
                $prefixField = $fieldset->addField($prefixElement->getId(), 'select', $prefixElement->getData(), '^');
                $prefixField->setValues($prefixOptions);
                if ($this->getAddressId()) {
                    $prefixField->addElementValues($this->getAddress()->getPrefix());
                }
            }
        }

        $suffixElement = $this->_form->getElement('suffix');
        if ($suffixElement) {
            $suffixOptions = $this->options->getNameSuffixOptions($this->getStore());
            if (!empty($suffixOptions)) {
                $fieldset->removeField($suffixElement->getId());
                $suffixField = $fieldset->addField(
                    $suffixElement->getId(),
                    'select',
                    $suffixElement->getData(),
                    $this->_form->getElement('lastname')->getId()
                );
                $suffixField->setValues($suffixOptions);
                if ($this->getAddressId()) {
                    $suffixField->addElementValues($this->getAddress()->getSuffix());
                }
            }
        }

        $regionElement = $this->_form->getElement('region_id');
        if ($regionElement) {
            $regionElement->setNoDisplay(true);
        }

        $this->_form->setValues($this->getFormValues());

        if ($this->_form->getElement('country_id')->getValue()) {
            $countryId = $this->_form->getElement('country_id')->getValue();
            $this->_form->getElement('country_id')->setValue(null);
            foreach ($this->_form->getElement('country_id')->getValues() as $country) {
                if ($country['value'] == $countryId) {
                    $this->_form->getElement('country_id')->setValue($countryId);
                }
            }
        }
        if ($this->_form->getElement('country_id')->getValue() === null) {
            $this->_form->getElement('country_id')->setValue(
                $this->directoryHelper->getDefaultCountry($this->getStore())
            );
        }

        $this->processCountryOptions($this->_form->getElement('country_id'));
        $this->processVatTypeOptions($this->_form->getElement('vat_type'));

        // Set custom renderer for VAT field if needed
        $vatIdElement = $this->_form->getElement('vat_id');
        if ($vatIdElement && $this->getDisplayVatValidationButton() !== false) {
            $vatIdElement->setRenderer(
                $this->getLayout()->createBlock(
                    'Magento\Customer\Block\Adminhtml\Sales\Order\Address\Form\Renderer\Vat'
                )->setJsVariablePrefix(
                    $this->getJsVariablePrefix()
                )
            );
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $countryElement
     * @return void
     */
    private function processVatTypeOptions(\Magento\Framework\Data\Form\Element\AbstractElement $vatTypeElement)
    {
	    $options = array(
        	array('value' => '1', 'label' => __('A Thai National')),
        	array('value' => '2', 'label' => __('A non-Thai National')),
        	array('value' => '3', 'label' => __('A Juristic Person')),
        );

	    $vatTypeElement->setValues($options);
    }

}
