<?php

namespace PPGroup\NewsletterPopup\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\SubscriberFactory;
use PPGroup\NewsletterPopup\Helper\Config as NewsletterPopupConfig;

class NewsletterPopup extends Template
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var NewsletterPopupConfig
     */
    protected $configHelper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @param Context $context
     * @param NewsletterPopupConfig $configHelper
     * @param Session $customerSession
     * @param SubscriberFactory $subscriberFactory
     * @param Json $jsonSerializer
     */
    public function __construct(
        Context $context,
        NewsletterPopupConfig $configHelper,
        Session $customerSession,
        SubscriberFactory $subscriberFactory,
        Json $jsonSerializer
    ) {
        parent::__construct($context);
        $this->configHelper = $configHelper;
        $this->customerSession = $customerSession;
        $this->subscriberFactory = $subscriberFactory;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Returns Newsletter Popup config
     *
     * @return string
     */
    public function getConfig(): string
    {
        $config = [
            'delay' => $this->_getPopupDelay(),
            'cookieName' => 'newsletter',
            'isSubscribed' => $this->isSubscribed() ? 1 : 0,
            'formSelector' => '#newsletter-validate-detail-popup'
        ];

        return $this->jsonSerializer->serialize($config);
    }

    /**
     * @return bool
     */
    public function isSubscribed(): bool
    {
        $subscriber = $this->subscriberFactory->create()->loadByCustomerId(
            $this->customerSession->getCustomerId()
        );
        if ($subscriber->getId()) {
            return $subscriber->isSubscribed();
        }

        return false;
    }

    /**
     * Newsletter Popup Text.
     *
     * @return string
     */
    public function getPopupText(): string
    {
        return $this->configHelper->getConfigParam(NewsletterPopupConfig::POPUP_TEXT);
    }

    /**
     * Newsletter Popup Delay.
     *
     * @return string
     */
    protected function _getPopupDelay(): string
    {
        return (string)$this->escapeHtml(
            $this->configHelper->getConfigParam(NewsletterPopupConfig::POPUP_DELAY)
        );
    }

    /**
     * Newsletter Popup Title.
     *
     * @return string
     */
    public function getPopupTitle(): string
    {
        return $this->configHelper->getConfigParam(NewsletterPopupConfig::POPUP_TITLE);
    }
}
