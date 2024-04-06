<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_OrderAttributes
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\OrderAttributes\Model\Order;

use IntlDateFormatter;
use function is_string;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Payment\Helper\Data;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Sales\Model\Order\Pdf\ItemsFactory;
use Magento\Sales\Model\Order\Pdf\Total\Factory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\RtlTextHandler;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\OrderAttributes\Helper\Data as DataHelper;
use Zend_Pdf_Color_GrayScale;
use Zend_Pdf_Color_Rgb;
use Zend_Pdf_Exception;
use Zend_Pdf_Page;

/**
 * Class InvoicePdf
 * @package Mageplaza\OrderAttributes\Model\Order
 */
class InvoicePdf extends Invoice
{
    /**
     * @var HelperPdf
     */
    protected $helperPdf;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var RtlTextHandler
     */
    protected $rtlTextHandler;

    /**
     * InvoicePdf constructor.
     *
     * @param Data $paymentData
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param Filesystem $filesystem
     * @param Config $pdfConfig
     * @param Factory $pdfTotalFactory
     * @param ItemsFactory $pdfItemsFactory
     * @param TimezoneInterface $localeDate
     * @param StateInterface $inlineTranslation
     * @param Renderer $addressRenderer
     * @param StoreManagerInterface $storeManager
     * @param Emulation $appEmulation
     * @param HelperPdf $helperPdf
     * @param DataHelper $dataHelper
     * @param RtlTextHandler|null $rtlTextHandler
     * @param array $data
     */
    public function __construct(
        Data $paymentData,
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        Config $pdfConfig,
        Factory $pdfTotalFactory,
        ItemsFactory $pdfItemsFactory,
        TimezoneInterface $localeDate,
        StateInterface $inlineTranslation,
        Renderer $addressRenderer,
        StoreManagerInterface $storeManager,
        Emulation $appEmulation,
        HelperPdf $helperPdf,
        DataHelper $dataHelper,
        ?RtlTextHandler $rtlTextHandler = null,
        array $data = []
    ) {
        $this->helperPdf  = $helperPdf;
        $this->dataHelper = $dataHelper;
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $storeManager,
            $appEmulation,
            $data
        );
        $this->rtlTextHandler = $rtlTextHandler ?: ObjectManager::getInstance()->get(RtlTextHandler::class);
    }

    /**
     * Insert order to pdf page. Copy form Core
     *
     * @param Zend_Pdf_Page $page
     * @param Order $obj
     * @param bool $putOrderId
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws Zend_Pdf_Exception
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($this->dataHelper->isDisplayAttributesInvoicePdf()) {
            if ($obj instanceof Order) {
                $shipment = null;
                $order    = $obj;
            } elseif ($obj instanceof Shipment) {
                $shipment = $obj;
                $order    = $shipment->getOrder();
            }

            $this->y = $this->y ? $this->y : 815;
            $top     = $this->y;

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.45));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.45));
            $page->drawRectangle(25, $top, 570, $top - 55);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
            $this->_setFontRegular($page, 10);

            if ($putOrderId) {
                $page->drawText(__('Order # ') . $order->getRealOrderId(), 35, $top -= 30, 'UTF-8');
                $top += 15;
            }

            $top -= 30;
            $page->drawText(
                __('Order Date: ') .
                $this->_localeDate->formatDate(
                    $this->_localeDate->scopeDate(
                        $order->getStore(),
                        $order->getCreatedAt(),
                        true
                    ),
                    IntlDateFormatter::MEDIUM,
                    false
                ),
                35,
                $top,
                'UTF-8'
            );

            $top -= 10;
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $top, 275, $top - 25);
            $page->drawRectangle(275, $top, 570, $top - 25);

            /* Calculate blocks info */

            /* Billing Address */
            $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

            /* Payment */
            $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
            $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
            $payment     = explode('{{pdf_row_separator}}', $paymentInfo);
            foreach ($payment as $key => $value) {
                if (strip_tags(trim($value)) == '') {
                    unset($payment[$key]);
                }
            }
            reset($payment);

            /* Shipping Address and Method */
            if (!$order->getIsVirtual()) {
                /* Shipping Address */
                $shippingAddress = $this->_formatAddress(
                    $this->addressRenderer->format($order->getShippingAddress(), 'pdf')
                );
                $shippingMethod  = $order->getShippingDescription();
            }

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->_setFontBold($page, 12);
            $page->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

            if (!$order->getIsVirtual()) {
                $page->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
            } else {
                $page->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
            }

            $addressesHeight = $this->_calcAddressHeight($billingAddress);
            if (isset($shippingAddress)) {
                $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
            }

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->_setFontRegular($page, 10);
            $this->y         = $top - 40;
            $addressesStartY = $this->y;

            foreach ($billingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $this->rtlTextHandler->reverseRtlText($_value);
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = $this->y;

            if (!$order->getIsVirtual()) {
                $this->y         = $addressesStartY;
                $shippingAddress = $shippingAddress ?? [];
                foreach ($shippingAddress as $value) {
                    if ($value !== '') {
                        $text = [];
                        foreach ($this->string->split($value, 45, true, true) as $_value) {
                            $text[] = $this->rtlTextHandler->reverseRtlText($_value);
                        }
                        foreach ($text as $part) {
                            $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                            $this->y -= 15;
                        }
                    }
                }

                $addressesEndY = min($addressesEndY, $this->y);
                $this->y       = $addressesEndY;

                $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(25, $this->y, 275, $this->y - 25);
                $page->drawRectangle(275, $this->y, 570, $this->y - 25);

                $this->y -= 15;
                $this->_setFontBold($page, 12);
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                $page->drawText(__('Payment Method:'), 35, $this->y, 'UTF-8');
                $page->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

                $this->y -= 10;
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));

                $this->_setFontRegular($page, 10);
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

                $paymentLeft = 35;
                $yPayments   = $this->y - 15;
            } else {
                $yPayments   = $addressesStartY;
                $paymentLeft = 285;
            }

            foreach ($payment as $value) {
                if (trim($value) != '') {
                    //Printing "Payment Method" lines
                    $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    }
                }
                $this->helperPdf->drawnAttributesOnly(
                    $page,
                    $order,
                    $this,
                    'payment_method',
                    $yPayments
                );
                $yPayments -= 15;
            }

            if ($order->getIsVirtual()) {
                // replacement of Shipments-Payments rectangle block
                $yPayments = min($addressesEndY, $yPayments);
                $page->drawLine(25, $top - 25, 25, $yPayments);
                $page->drawLine(570, $top - 25, 570, $yPayments);
                $page->drawLine(25, $yPayments, 570, $yPayments);

                $this->y = $yPayments - 15;
            } else {
                $topMargin    = 15;
                $methodStartY = $this->y;
                $this->y      -= 15;

                if (isset($shippingMethod) && is_string($shippingMethod)) {
                    foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                        $page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }

                $yShipments               = $this->y;
                $totalShippingChargesText = "("
                    . __('Total Shipping Charges')
                    . " "
                    . $order->formatPriceTxt($order->getShippingAmount())
                    . ")";

                $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
                $yShipments -= $topMargin + 10;

                $tracks = [];
                if ($shipment) {
                    $tracks = $shipment->getAllTracks();
                }
                if (count($tracks)) {
                    $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                    $page->setLineWidth(0.5);
                    $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                    $page->drawLine(400, $yShipments, 400, $yShipments - 10);
                    //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                    $this->_setFontRegular($page, 9);
                    $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                    //$page->drawText(__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                    $page->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                    $page->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                    $yShipments -= 20;
                    $this->_setFontRegular($page, 8);
                    foreach ($tracks as $track) {
                        $maxTitleLen    = 45;
                        $endOfTitle     = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                        $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                        $page->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                        $page->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    }
                }
                $this->helperPdf->drawnAttributesOnly(
                    $page,
                    $order,
                    $this,
                    'shipping_method',
                    $yShipments
                );
                $yShipments -= $topMargin - 5;

                $currentY = min($yPayments, $yShipments);
                // replacement of Shipments-Payments rectangle block
                $page->drawLine(25, $methodStartY, 25, $currentY);
                //left
                $page->drawLine(25, $currentY, 570, $currentY);
                //bottom
                $page->drawLine(570, $currentY, 570, $methodStartY);
                //right

                $this->y = $currentY;
                $this->y -= 15;
            }
            $this->helperPdf->drawnAttributes($page, $order, $this, 'additional');
        } else {
            parent::insertOrder($page, $obj, $putOrderId);
        }
    }
}
