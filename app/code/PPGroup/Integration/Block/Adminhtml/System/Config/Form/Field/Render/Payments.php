<?php


namespace PPGroup\Integration\Block\Adminhtml\System\Config\Form\Field\Render;

use Magento\Framework\View\Element\Context;
use Magento\Payment\Model\Config;

class Payments extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * Method List
     *
     * @var array
     */
    protected $paymentModelConfig;


    /**
     * Scope Config
     *
     * @var array
     */
    protected $scopeConfig;


    /**
     * Constructor
     *
     * @param Context $context Context
     * @param Config $paymentModelConfig Payment ModelConfig
     * @param array $data Data
     */
    public function __construct(
        Context $context,
        Config $paymentModelConfig,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->paymentModelConfig = $paymentModelConfig;
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
            $payments = $this->paymentModelConfig->getActiveMethods();
            $methods = array();
            foreach ($payments as $paymentCode => $paymentModel) {
                $paymentTitle = $this->scopeConfig
                    ->getValue('payment/' . $paymentCode . '/title');
                $this->addOption($paymentCode, $paymentTitle);
            }
        }
        return parent::_toHtml();
    }

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
