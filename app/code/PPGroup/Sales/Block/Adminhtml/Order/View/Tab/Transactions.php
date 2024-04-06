<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PPGroup\Sales\Block\Adminhtml\Order\View\Tab;

/**
 * Order transactions tab
 *
 * @api
 * @since 100.0.2
 */
class Transactions extends \Magento\Framework\View\Element\Text\ListText implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    const QR_CODE_PAYMENT_METHOD = "qrcode";

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_authorization = $authorization;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Transactions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Transactions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        if ($this->getOrder()->getPayment()->getMethodInstance()->getCode() == self::QR_CODE_PAYMENT_METHOD) {
            return true;
        } else {
            return !$this->getOrder()->getPayment()->getMethodInstance()->isOffline();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return !$this->_authorization->isAllowed('Magento_Sales::transactions_fetch');
    }
}
