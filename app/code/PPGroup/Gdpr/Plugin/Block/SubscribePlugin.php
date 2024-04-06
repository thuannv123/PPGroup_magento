<?php

namespace PPGroup\Gdpr\Plugin\Block;

use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Newsletter\Block\Subscribe as SubscribeBlock;

class SubscribePlugin
{
    public function beforeToHtml(SubscribeBlock $subject)
    {
        if (!$subject->getData('consent_checkbox')) {
            $layout = $subject->getLayout();
            $checkboxBlock = $layout->createBlock(
                \Amasty\Gdpr\Block\Checkbox::class,
                '',
                [
                    'scope' => ConsentLogger::FROM_SUBSCRIPTION
                ]
            )->setTemplate('Amasty_Gdpr::checkbox.phtml')->toHtml();
            $subject->setConsentCheckbox($checkboxBlock);
        }
    }
}
