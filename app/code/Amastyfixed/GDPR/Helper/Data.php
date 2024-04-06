<?php

namespace Amastyfixed\GDPR\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const XML_PATH_CHECKBOX_CODE = 'amasty_gdpr/subscribe/checkbox_list';
    const XML_PATH_DECLINE_CHECKBOX_CODE = 'amasty_gdpr/unsubscribe/multicheckbox_list';

    protected $scopeConfigInterface;

    public function __construct(
        Context $context
    )
    {
        $this->scopeConfigInterface = $context->getScopeConfig();
        parent::__construct($context);
    }

    public function getCheckBoxCode()
    {
        return $this->scopeConfigInterface->getValue(self::XML_PATH_CHECKBOX_CODE);
    }

    public function getDeclineCheckboxCode()
    {
        return $this->scopeConfigInterface->getValue(self::XML_PATH_DECLINE_CHECKBOX_CODE);
    }
}