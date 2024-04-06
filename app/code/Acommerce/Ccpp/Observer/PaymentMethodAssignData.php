<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 * PHP version 5
 *
 * @category Acommerce_Sales
 * @package  Acommerce
 * @author   Por <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */
namespace Acommerce\Ccpp\Observer;

use Magento\Framework\Event\ObserverInterface;
use Acommerce\Ccpp\Model\Adminhtml\Source\PaymentOption;

/**
 * Export Sales Order To Cpms
 *
 * @category Acommerce_Sales
 * @package  Acommerce
 * @author   Por <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */
class PaymentMethodAssignData implements ObserverInterface
{
    /**
     * Update Order state to invoiced
     *
     * @param \Magento\Framework\Event\Observer $observer Observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $method = $observer->getMethod();
        $data = $observer->getData('data');
        $infoInstance = $observer->getPaymentModel();

        $additionalData = $data->getData();

        if ($additionalData) {
            if (isset($additionalData['additional_data']['installment'])
                && $additionalData['additional_data']['installment'] == 1
                && (int) $additionalData['additional_data']['installment_month'] > 0
            ) {
                $data->setData(
                    'additional_information',
                    array(
                        'installment_month' =>
                            $additionalData['additional_data']['installment_month'],
                        'installment' =>
                            $additionalData['additional_data']['installment']
                        )
                );

                $infoInstance->setAdditionalInformation(
                    'installment_month',
                    $additionalData['additional_data']['installment_month']
                );

                $infoInstance->setAdditionalInformation(
                    'installment',
                    $additionalData['additional_data']['installment']
                );
            }
        }
    }
}
