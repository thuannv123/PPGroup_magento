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

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Mageplaza\OrderAttributes\Helper\Data as DataHelper;
use Mageplaza\OrderAttributes\Model\Attribute;
use Mageplaza\OrderAttributes\Model\ResourceModel\Attribute\Collection;
use Mageplaza\OrderAttributes\Model\ResourceModel\Attribute\CollectionFactory;
use Zend_Pdf_Color_GrayScale;
use Zend_Pdf_Color_Rgb;
use Zend_Pdf_Exception;
use Zend_Pdf_Font;
use Zend_Pdf_Image;
use Zend_Pdf_Page;
use Zend_Pdf_Resource_Font;

/**
 * Class HelperPdf
 * @package Mageplaza\OrderAttributes\Model\Order
 */
class HelperPdf
{
    /**
     * @var DataHelper
     */
    protected $dataHelper;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Serializer interface instance.
     *
     * @var Json
     */
    private $serializer;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var Escaper
     */
    protected $escaper;
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var InvoicePdf
     */
    protected $pdf;

    /**
     * @var int
     */
    protected $yBackFirstLine;

    /**
     * @var ReadInterface
     */
    protected $_rootDirectory;
    /**
     * @var int
     */
    private $currentY;
    /**
     * @var string
     */
    private $position;
    /**
     * @var int
     */
    protected $y;

    /**
     * InvoicePdf constructor.
     *
     * @param Filesystem $filesystem
     * @param DataHelper $dataHelper
     * @param CollectionFactory $collectionFactory
     * @param Json $json
     * @param UrlInterface $urlBuilder
     * @param ManagerInterface $messageManager
     * @param Escaper $esCaper
     */
    public function __construct(
        Filesystem $filesystem,
        DataHelper $dataHelper,
        CollectionFactory $collectionFactory,
        Json $json,
        UrlInterface $urlBuilder,
        ManagerInterface $messageManager,
        Escaper $esCaper
    ) {
        $this->dataHelper        = $dataHelper;
        $this->collectionFactory = $collectionFactory;
        $this->serializer        = $json;
        $this->urlBuilder        = $urlBuilder;
        $this->messageManager    = $messageManager;
        $this->escaper           = $esCaper;
        $this->filesystem        = $filesystem;
        $this->_rootDirectory    = $filesystem->getDirectoryRead(DirectoryList::ROOT);
    }

    /**
     * @param Zend_Pdf_Page $page
     * @param Order $obj
     * @param $pdf
     * @param $position
     * @param $y
     */
    public function drawnAttributesOnly(Zend_Pdf_Page &$page, Order &$obj, &$pdf, $position, &$y)
    {
        $this->currentY = &$currentY;
        $this->pdf      = &$pdf;
        $this->position = $position;
        $this->y        = &$y;
        $sEncoding      = 'UTF-8';
        $distanceDrawn  = 220;
        $xTextStart     = 30;
        if ($position === 'payment_method') {
            $xTextStart    = 35;
            $distanceDrawn = 220;
        }
        if ($position === 'shipping_method') {
            $xTextStart    = 285;
            $distanceDrawn = 250;
        }
        try {
            $storeId    = $obj->getStoreId();
            $attributes = $this->getAttributes();
            /** @var Attribute $attribute */
            foreach ($attributes->getItems() as $attribute) {
                if (!$this->dataHelper->isVisible($attribute, $storeId, null)
                    || $attribute->getFrontendInput() === 'cms_block') {
                    continue;
                }
                $label = $this->getLabel($attribute, $storeId);
                $value = $obj->getData($attribute->getAttributeCode());
                if ($value !== null) {
                    $value = $this->getValue($attribute, $storeId, $value, $page);
                } else {
                    continue;
                }
                $text          = $label . ": " . $value;
                $frontendInput = $attribute->getFrontendInput();
                if ($value || $frontendInput === 'image') {
                    $textLines = $this->processText($page, $text, $distanceDrawn, $frontendInput);
                    foreach ($textLines as $txt) {
                        if ($frontendInput !== 'image' || $value) {
                            $y -= 15;
                        }
                        $page->drawText($txt, $xTextStart, $y, $sEncoding);
                    }
                }
            }
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * @param Zend_Pdf_Page $page
     * @param string $text
     *
     * @param $distanceDrawn
     * @param $frontendInput
     *
     * @return array
     * @throws Zend_Pdf_Exception
     */
    public function processText($page, $text, $distanceDrawn, $frontendInput)
    {
        if ($frontendInput === 'textarea_visual') {
            $text = strip_tags($text);
        }
        $text      = explode(' ', $text);
        $textDrawn = '';
        $textLines = [];
        $textTemp  = '';
        foreach ($text as $key => $txt) {
            $txt = $txt . ' ';
            if ($textTemp) {
                $textDrawn .= $textTemp;
                $textTemp  = '';
            }
            $textDistance = $this->getWidthForStringUsingFontSize($page, $textDrawn . $txt);
            if ($textDistance >= $distanceDrawn) {
                if ($textDistance >= $distanceDrawn + 40) {
                    $textLines[] = $textDrawn;
                    $textTemp    = $txt;
                    if ($key === count($text) - 1) {
                        $textLines[] = $txt;
                    }
                } else {
                    $textLines[] = $textDrawn . $txt;
                }
                $textDrawn = '';
            } else {
                $textDrawn .= $txt;
                if ($key === count($text) - 1) {
                    $textLines[] = $textDrawn;
                }
            }
        }

        return $textLines;
    }

    /**
     * @param Zend_Pdf_Page $page
     * @param Order $obj
     * @param InvoicePdf | ShipmentPdf $pdf
     * @param null $position
     *
     * @return int
     */
    public function drawnAttributes(Zend_Pdf_Page &$page, Order &$obj, &$pdf, $position = null)
    {
        $this->position       = $position;
        $this->pdf            = &$pdf;
        $fontSize             = 10;
        $iWidthBorder         = 545; // half page width
        $sEncoding            = 'UTF-8';
        $this->yBackFirstLine = 0; // move down on page
        $yBackFirstLine       = &$this->yBackFirstLine; // move down on page
        try {
            if (count($this->getAttributes()) > 0) {
                $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.45));
                $iXCoordinateBorder = 25; // border is wider than text
                // draw top border
                $page->drawLine($iXCoordinateBorder, $this->pdf->y, $iXCoordinateBorder + $iWidthBorder, $this->pdf->y);
                $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->drawRectangle(25, $this->pdf->y, 570, $this->pdf->y - 20);
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                $this->_setFontBold($page, $fontSize + 2);
                $this->pdf->y   -= 15;
                $yBackFirstLine += 15;
                $page->drawText(__('Additional Information'), $iXCoordinateBorder + 5, $this->pdf->y, $sEncoding);
                $this->_setFontRegular($page, $fontSize);
                $this->drawnAttributesContent($page, $obj, $iXCoordinateBorder, $sEncoding);
                $yBackFirstLine += 10;
                // draw bottom border
                $page->drawLine($iXCoordinateBorder, $this->pdf->y, $iXCoordinateBorder + $iWidthBorder, $this->pdf->y);
                // draw left border
                $page->drawLine(
                    $iXCoordinateBorder,
                    $this->pdf->y,
                    $iXCoordinateBorder,
                    $this->pdf->y + $yBackFirstLine /* back to first line */
                );
                // draw right border
                $page->drawLine(
                    $iXCoordinateBorder + $iWidthBorder,
                    $this->pdf->y,
                    $iXCoordinateBorder + $iWidthBorder,
                    $this->pdf->y + $yBackFirstLine /* back to first line */
                );
                $this->pdf->y -= 10;
            }
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->pdf->y;
    }

    /**
     * @param Zend_Pdf_Page $page
     * @param Order $order
     * @param $xTextStart
     * @param $sEncoding
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Zend_Pdf_Exception
     */
    private function drawnAttributesContent($page, $order, $xTextStart, $sEncoding)
    {
        $this->pdf->y -= 10;
        $storeId      = $order->getStoreId();
        $attributes   = $this->getAttributes();
        /** @var Attribute $attribute */
        foreach ($attributes->getItems() as $attribute) {
            if (!$this->dataHelper->isVisible($attribute, $storeId, null)
                || $attribute->getFrontendInput() === 'cms_block') {
                continue;
            }
            $label = $this->getLabel($attribute, $storeId);
            $value = $order->getData($attribute->getAttributeCode());
            if ($value !== null) {
                $value = $this->getValue($attribute, $storeId, $value, $page);
            } else {
                continue;
            }
            $text          = $label . ": " . $value;
            $frontendInput = $attribute->getFrontendInput();
            if ($value || $frontendInput === 'image') {
                $textLines = $this->processText($page, $text, 500, $frontendInput);
                foreach ($textLines as $txt) {
                    if ($frontendInput !== 'image' || $value) {
                        $this->pdf->y         -= 15;
                        $this->yBackFirstLine += 15;
                    }
                    $page->drawText($txt, $xTextStart + 5, $this->pdf->y, $sEncoding);
                }
            }
        }
        $this->pdf->y -= 10;
    }

    /**
     *
     * @return Collection
     */
    public function getAttributes()
    {
        $attributes = $this->collectionFactory->create();
        $position   = $this->position;
        if ($position === 'additional') {
            $allPositions = '(1, 2, 3, 4, 5, 6)';
            $attributes->getSelect()
                ->where("position NOT IN {$allPositions}")->orWhere("position IN (1,6)");
        } else {
            if ($position === 'shipping_method') {
                $position = [2, 3];
            } elseif ($position === 'payment_method') {
                $position = [4, 5];
            }
            $attributes = $attributes->addFieldToFilter('position', ['in' => $position]);
        }

        return $attributes;
    }

    /**
     * @param $attribute
     * @param $storeId
     *
     * @return mixed
     */
    private function getLabel($attribute, $storeId)
    {
        $labels = $this->serializer->unserialize($attribute->getLabels());

        return !empty($labels[$storeId]) ? $labels[$storeId] : $attribute->getFrontendLabel();
    }

    /**
     * @param Attribute $attribute
     * @param $storeId
     * @param $value
     * @param Zend_Pdf_Page $page
     *
     * @return string
     * @throws Zend_Pdf_Exception
     */
    private function getValue($attribute, $storeId, $value, $page)
    {
        $result        = $value;
        $frontendInput = $attribute->getFrontendInput();
        switch ($frontendInput) {
            case 'text':
            case 'textarea':
                $result = $this->escapeHtml($value);
                break;
            case 'boolean':
                $result = $this->dataHelper->prepareBoolValue($value);
                break;
            case 'select':
            case 'multiselect':
            case 'select_visual':
            case 'multiselect_visual':
                $result = $this->dataHelper->prepareOptionValue($attribute->getOptions(), $value, $storeId);
                break;
            case 'date':
                $result = $this->dataHelper->prepareDateValue($value);
                break;
            case 'file':
                $this->drawUrlBox($page, $value, $attribute, $storeId);
                $result = substr($value, strrpos($value, '/') + 1);
                break;
            case 'image':
                if (is_array(getimagesize($this->getPathImg($value)))) {
                    $this->dawnImg($page, $value, $attribute, $storeId);
                    $result = '';
                } else {
                    $this->drawUrlBox($page, $value, $attribute, $storeId);
                    $result = substr($value, strrpos($value, '/') + 1);
                }
                break;
        }

        return $result;
    }

    /**
     * @param $page
     * @param $value
     * @param $attribute
     * @param $storeId
     *
     * @throws Zend_Pdf_Exception
     */
    public function dawnImg($page, $value, $attribute, $storeId)
    {
        $image      = Zend_Pdf_Image::imageWithPath($this->getPathImg($value));
        $imageW     = $image->getPixelWidth();
        $imageH     = $image->getPixelHeight();
        $labelEnd   = 40;
        $imageStart = $labelEnd +
            $this->getWidthForStringUsingFontSize(
                $page,
                $this->getLabel($attribute, $storeId)
            );
        if ($imageH > 120) { // set max height is 120
            $rate   = (float) 120 / $imageH;
            $imageW = (int) $imageW * $rate;
            $imageH = (int) $imageH * $rate;
        }
        if ($this->position === 'additional') {
            $this->yBackFirstLine += $imageH + 10;
            $this->pdf->y         -= $imageH + 10;
            $page->drawImage(
                $image,
                $imageStart,
                $this->pdf->y,
                $imageStart + $imageW,
                $this->pdf->y + $imageH
            );
        } else {
            $this->y -= $imageH + 10;
            $page->drawImage(
                $image,
                $imageStart + 5,
                $this->y,
                $imageStart + 5 + $imageW,
                $this->y + $imageH
            );
        }
    }

    /**
     * @param $page
     * @param $value
     * @param $attribute
     * @param $storeId
     * @return void
     * @throws Zend_Pdf_Exception
     */
    public function drawUrlBox($page, $value, $attribute, $storeId)
    {
        $xTextStart    = $this->position === 'shipping_method' ? 285 : 35;
        $distanceDrawn = $this->position === 'additional' ? 500 : ($this->position === 'shipping_method' ? 250 : 220);

        $label         = $this->getLabel($attribute, $storeId) . ': ';
        $urlText       = substr($value, strrpos($value, '/') + 1);
        $labelLength   = $this->getWidthForStringUsingFontSize($page, $label);
        $textLength    = $this->getWidthForStringUsingFontSize($page, $urlText);
        $urlStart      = $labelLength + $xTextStart;
        $urlEnd        = $urlStart + $textLength;
        $y             = $this->position === 'additional' ? $this->pdf->y : $this->y;

        if ($textLength > $distanceDrawn + 40) {
            $y -= 15;
        }
        $url           = $this->urlBuilder->getUrl('mporderattributes/viewfile/file', ['f' => substr($value, 5)]);
        $target        = \Zend_Pdf_Action_URI::create($url);
        $annotation = \Zend_Pdf_Annotation_Link::create(
            $urlStart,
            $y -5,
            $urlEnd,
            $y -15,
            $target
        );

        $page->attachAnnotation($annotation);
    }

    /**
     * Escape HTML entities
     *
     * @param string|array $data
     * @param array|null $allowedTags
     *
     * @return string
     * @deprecated 103.0.0 Use $escaper directly in templates and in blocks.
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return $this->escaper->escapeHtml($data, $allowedTags);
    }

    /**
     * @param $file
     *
     * @return string
     */
    private function getPathImg($file)
    {
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $fileName  = DataHelper::TEMPLATE_MEDIA_PATH . '/' . ltrim($file, '/');

        return $directory->getAbsolutePath($fileName);
    }

    /**
     * @param $page
     * @param string $text
     *
     * @return float|int
     * @throws Zend_Pdf_Exception
     */
    public function getWidthForStringUsingFontSize($page, $text)
    {
        return $this->pdf->widthForStringUsingFontSize(
            $text,
            $this->_setFontRegular($page, 10),
            10
        );
    }

    /**
     * Set font as regular
     *
     * @param Zend_Pdf_Page $object
     * @param int $size
     *
     * @return Zend_Pdf_Resource_Font
     * @throws Zend_Pdf_Exception
     */
    protected function _setFontRegular($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/GnuFreeFont/FreeSerif.ttf')
        );
        $object->setFont($font, $size);

        return $font;
    }

    /**
     * Set font as bold
     *
     * @param Zend_Pdf_Page $object
     * @param int $size
     *
     * @return Zend_Pdf_Resource_Font
     * @throws Zend_Pdf_Exception
     */
    protected function _setFontBold($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/GnuFreeFont/FreeSerifBold.ttf')
        );
        $object->setFont($font, $size);

        return $font;
    }
}
