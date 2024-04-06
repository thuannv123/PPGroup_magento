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

namespace PPGroup\OrderAttributes\Override\Block\Order\View;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\OrderAttributes\Helper\Data;
use Mageplaza\OrderAttributes\Model\Attribute;
use Mageplaza\OrderAttributes\Model\ResourceModel\Attribute\CollectionFactory;
use Mageplaza\OrderAttributes\Block\Order\View\Additional as MageplazaOrderAdditionals;

/**
 * Class Additional
 * @package PPGroup\OrderAttributes\Override\Block\Order\View
 */
class Additional extends MageplazaOrderAdditionals
{
    /**
     * Path Of Template
     *
     * @var string
     */
    protected $_template = 'Mageplaza_OrderAttributes::order/view/additional.phtml';

    /**
     * @var EncoderInterface
     */
    private $urlEncoder;

    /**
     * Serializer interface instance.
     *
     * @var Json
     */
    private $serializer;

    /**
     * Additional constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Data $dataHelper
     * @param EncoderInterface $urlEncoder
     * @param CollectionFactory $collectionFactory
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $dataHelper,
        EncoderInterface $urlEncoder,
        CollectionFactory $collectionFactory,
        Json $json,
        array $data = []
    ) {
        $this->urlEncoder        = $urlEncoder;
        $this->serializer        = $json;
        parent::__construct($context, $registry, $dataHelper, $urlEncoder, $collectionFactory, $json, $data);    }

    /**
     * @param $position
     * @param string $area
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAttributes($position, $area = 'admin')
    {
        $result = '';

        $attributeList = [];

        if ($order = $this->getOrder()) {
            $storeId = $order->getStoreId();
            if ($position === 'steps') {
                $allPositions = [1, 2, 3, 4, 5, 6];
                $attributes   = $this->collectionFactory->create()
                    ->addFieldToFilter('position', ['nin' => $allPositions]);
            } else {
                $attributes = $this->collectionFactory->create()->addFieldToFilter('position', ['in' => $position]);
            }

            if ($attributes->getSize() > 0) {
                foreach ($attributes->getItems() as $attribute) {
                    $attributeList[] = $attribute;
                }

                usort($attributeList, function ($a, $b) {
                    return strcmp($a->getSortOrder(), $b->getSortOrder());
                });

                /** @var Attribute $attribute */
                foreach ($attributeList as $attribute) {
                    if (!$this->dataHelper->isVisible($attribute, $storeId, null)
                        || ($area === 'customer' && !$attribute->getShowInFrontendOrder())
                        || $attribute->getFrontendInput() === 'cms_block') {
                        continue;
                    }

                    $label = $this->getLabel($attribute, $storeId);
                    $value = $order->getData($attribute->getAttributeCode());

                    if ($value !== null) {
                        $class = $attribute->getFrontendInput() . '-attribute';
                        $value = $this->getValue($attribute, $storeId, $value);
                        $result .= '<strong>' . $label . ': </strong>';
                        $result .= "<span class='" . $class . "'>" . $value . '</span><br>';
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAttributesInCustomSteps()
    {
        return $this->getAttributes('steps');
    }

    /**
     * @param Attribute $attribute
     * @param $storeId
     *
     * @return string
     */
    private function getLabel($attribute, $storeId)
    {
        $labels = $this->serializer->unserialize($attribute->getLabels());
        $label  = !empty($labels[$storeId]) ? $labels[$storeId] : $attribute->getFrontendLabel();

        return $label;
    }

    /**
     * @param Attribute $attribute
     * @param $storeId
     * @param $value
     *
     * @return string
     */
    private function getValue($attribute, $storeId, $value)
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
                $param  = '/' . $frontendInput . '/' . $this->urlEncoder->encode($value);
                $path   = $this->_urlBuilder->getUrl('mporderattributes/viewfile/index' . $param);
                $result = '<a target="_blank" href="' . $path . '">'
                    . substr($value, strrpos($value, '/') + 1) . '</a>';
                break;
            case 'image':
                $param  = '/' . $frontendInput . '/' . $this->urlEncoder->encode($value);
                $path   = $this->_urlBuilder->getUrl('mporderattributes/viewfile/index' . $param);
                if (is_array(getimagesize($path))) {
                    $result = '<br/><a target="_blank" href="' . $path . '">';
                    $result .= '<img style="max-height: 100px" title="' . __('View Full Size') . '" src="' . $path . '" alt="' . $value . '">';
                    $result .= '</a>';
                } else {
                    $param  = '/' . 'file' . '/' . $this->urlEncoder->encode($value);
                    $path   = $this->_urlBuilder->getUrl('mporderattributes/viewfile/index' . $param);
                    $result = '<a target="_blank" href="' . $path . '">'
                        . substr($value, strrpos($value, '/') + 1) . '</a>';
                }
                break;
        }

        return $result;
    }

    /**
     * Get current order
     *
     * @return mixed
     */
    private function getOrder()
    {
        $actionName = $this->getRequest()->getFullActionName();
        if ($actionName === 'sales_order_invoice_view' || $actionName === 'sales_order_invoice_new') {
            return $this->registry->registry('current_invoice')->getOrder();
        }
        if ($actionName === 'adminhtml_order_shipment_view' || $actionName === 'adminhtml_order_shipment_new') {
            return $this->registry->registry('current_shipment')->getOrder();
        }

        return $this->registry->registry('current_order');
    }
}
