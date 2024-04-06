<?php

namespace PPGroup\Integration\Block\Adminhtml\System\Config\Form\Field\Render;

use Magento\Framework\View\Element\Context;
use \Magento\Shipping\Model\Config;

class Shippings extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * Method List
     *
     * @var array
     */
    protected $shippingModelConfig;
    protected $scopeConfig;


    /**
     * Constructor
     *
     * @param Context $context Context
     * @param Config $shippingModelConfig Shipping Model Config
     * @param array $data Data
     */
    public function __construct(
        Context $context,
        Config $shippingModelConfig,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->shippingModelConfig = $shippingModelConfig;
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $shippings = $this->shippingModelConfig->getActiveCarriers();
            $methods = array();
            foreach ($shippings as $shippingCode => $shippingModel) {
                $shippingTitle = $this->scopeConfig
                    ->getValue('carriers/' . $shippingCode . '/title');
                $this->addOption($shippingCode, $shippingTitle);
            }
        }
        return parent::_toHtml();
    }
    // @codingStandardsIgnoreEnd

    /**
     * Sets name for input element
     *
     * @param string $value Value
     *
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
