<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PPGroup\Sales\Model\Order\Email\Sender;

use Magento\Sales\Model\Order\Shipment;
use Magento\Framework\DataObject;

/**
 * Sends order shipment email to the customer.
 *
 * @deprecated 102.1.0 since this class works only with the concrete model and no data interface
 * @see \Magento\Sales\Model\Order\Shipment\Sender\EmailSender
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShipmentSender extends \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
{

    /**
     * Sends order shipment email to the customer.
     *
     * Email will be sent immediately in two cases:
     *
     * - if asynchronous email sending is disabled in global settings
     * - if $forceSyncMode parameter is set to TRUE
     *
     * Otherwise, email will be sent later during running of
     * corresponding cron job.
     *
     * @param Shipment $shipment
     * @param bool $forceSyncMode
     * @return bool
     * @throws \Exception
     */
    public function send(Shipment $shipment, $forceSyncMode = false)
    {
        $shipment->setSendEmail($this->identityContainer->isEnabled());

        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            $order = $shipment->getOrder();
            $this->identityContainer->setStore($order->getStore());

            $transport = [
                'order' => $order,
                'order_id' => $order->getId(),
                'shipment' => $shipment,
                'shipment_id' => $shipment->getId(),
                'comment' => $shipment->getCustomerNoteNotify() ? $shipment->getCustomerNote() : '',
                'billing' => $order->getBillingAddress(),
                'payment_html' => $this->getPaymentHtml($order),
                'store' => $order->getStore(),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
                'order_data' => [
                    'customer_name' => $order->getCustomerName(),
                    'is_not_virtual' => $order->getIsNotVirtual(),
                    'email_customer_note' => $order->getEmailCustomerNote(),
                    'frontend_status_label' => $order->getFrontendStatusLabel(),
                    'created_at_formatted' => $order->getCreatedAtFormatted(2)
                ]
            ];
            $transportObject = new DataObject($transport);

            /**
             * Event argument `transport` is @deprecated. Use `transportObject` instead.
             */
            $this->eventManager->dispatch(
                'email_shipment_set_template_vars_before',
                ['sender' => $this, 'transport' => $transportObject->getData(), 'transportObject' => $transportObject]
            );

            $this->templateContainer->setTemplateVars($transportObject->getData());

            if ($this->checkAndSend($order)) {
                $shipment->setEmailSent(true);
                $this->shipmentResource->saveAttribute($shipment, ['send_email', 'email_sent']);
                return true;
            }
        } else {
            $shipment->setEmailSent(null);
            $this->shipmentResource->saveAttribute($shipment, 'email_sent');
        }

        $this->shipmentResource->saveAttribute($shipment, 'send_email');

        return false;
    }
}
