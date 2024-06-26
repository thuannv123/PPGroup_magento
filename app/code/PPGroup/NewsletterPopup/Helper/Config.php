<?php

namespace PPGroup\NewsletterPopup\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const BASE_CONFIG_XML_PREFIX = 'ppgroup_newsletterpopup/settings/%s';
    const POPUP_DELAY = 'popup_delay';
    const POPUP_TITLE = 'popup_title';
    const POPUP_TEXT = 'popup_text';

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Return configuration param from module admin settings
     *
     * @param string $configField
     * @return mixed
     */
    public function getConfigParam($configField)
    {
        return $this->scopeConfig->getValue(
            sprintf(self::BASE_CONFIG_XML_PREFIX, $configField),
            ScopeInterface::SCOPE_STORE
        );

    }

}
