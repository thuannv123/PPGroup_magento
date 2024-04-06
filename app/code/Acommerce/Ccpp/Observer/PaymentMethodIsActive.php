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
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Export Sales Order To Cpms
 *
 * @category Acommerce_Sales
 * @package  Acommerce
 * @author   Por <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */
class PaymentMethodIsActive implements ObserverInterface
{

    /**
     *  Product Meta Data
     *
     * @var Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Constructor
     *
     * @param ProductMetadataInterface $productMetadata Checkout Session
     */
    public function __construct(
        ProductMetadataInterface $productMetadata
    ) {

        $this->productMetadata = $productMetadata;
    }

    /**
     * Update Order state to invoiced
     *
     * @param \Magento\Framework\Event\Observer $observer Observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $result = $observer->getResult();
        $methodInstance = $observer->getMethodInstance();
        $quote = $observer->getQuote();

        if ($methodInstance->getCode() == 'ccpp'
            && $result->getData('is_available')
            && !is_null($quote)
        ) {
            $paymentOption = $methodInstance->getConfigData('payment_option');
            $interestType = $methodInstance->getConfigData('ipp_interest_type');
            $installments = $methodInstance->getConfigData('installments');

            if ($paymentOption == PaymentOption::IPP) {

                if(version_compare($this->productMetadata->getVersion(), '2.2.0',  '>=')) {
                    $installments = json_decode(trim($installments), true);
                } else {
                    $installments = unserialize(trim($installments));
                }

                if ($installments) {
                    $grandTotal = $quote->getGrandTotal();

                    foreach ($installments as $key => $installment) {
                        $minAmount = (float) $installment['min_amount'];
                        $maxAmount = (float) $installment['max_amount'];

                        if (!($minAmount <= $grandTotal
                            && $maxAmount >= $grandTotal)
                        ) {
                            unset($installments[$key]);
                        }
                    }

                    if (count($installments) == 0) {
                        $result->setData('is_available', false);
                    }
                } else {
                    $result->setData('is_available', false);
                }
            }
        }
    }
}
