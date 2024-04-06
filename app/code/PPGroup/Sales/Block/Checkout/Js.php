<?php
namespace PPGroup\Sales\Block\Checkout;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;

class Js extends Template
{
    /**
     * Path Of Template
     *
     * @var string
     */
    protected $_template = 'PPGroup_Sales::js.phtml';
    public function __construct(
      Template\Context $context,
      ScopeConfigInterface $scopeConfig,
      array $data = []
      )
    {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }
    public function getRequestTaxInvoiceOptionalYesNo()
    {
        return $this->_scopeConfig->getValue("checkout/options/request_tax_invoice_enabled");
    }
}