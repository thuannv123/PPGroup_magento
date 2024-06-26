<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sales Order Email order items
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace PPGroup\Sales\Block\Order\Email;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Registry;
/**
 * Sales Order Email items.
 *
 * @api
 * @since 100.0.2
 */
class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    protected Registry $registry;
    /**
     * @param Context $context
     * @param array $data
     * @param OrderRepositoryInterface|null $orderRepository
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = [],
        ?OrderRepositoryInterface $orderRepository = null
    ) {
        $this->registry = $registry;
        $this->orderRepository = $orderRepository ?: ObjectManager::getInstance()->get(OrderRepositoryInterface::class);

        parent::__construct($context, $data);
    }

    /**
     * Returns order.
     *
     * Custom email templates are only allowed to use scalar values for variable data.
     * So order is loaded by order_id, that is passed to block from email template.
     * For legacy custom email templates it can pass as an object.
     *
     * @return OrderInterface|null
     * @since 102.1.0
     */
    public function getOrder()
    {
        $order = $this->getData('order');

        if ($order !== null) {
            return $order;
        }
        $orderId = (int)$this->getData('order_id');
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            $this->setData('order', $order);
        }

        return $this->getData('order');
    }

    /**
     * @return string
     */
    public function getCreatedAtFormatted(): string
    {
        $format = $this->getCreatedAtFormat();
        return $this->getOrder()->getCreatedAtFormatted($format);
    }

    /**
     * Retrieve invoice order
     *
     * @return string
     */
    public function getCreatedAtFormat()
    {
        return $this->registry->registry('created_at_format');
    }
}
