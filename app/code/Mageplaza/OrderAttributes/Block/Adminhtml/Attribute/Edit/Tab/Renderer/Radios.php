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

namespace Mageplaza\OrderAttributes\Block\Adminhtml\Attribute\Edit\Tab\Renderer;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Radios as ElemRadios;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Class Radios
 * @package Mageplaza\OrderAttributes\Block\Adminhtml\Attribute\Edit\Tab\Renderer
 */
class Radios extends ElemRadios
{
    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->secureRenderer
            = $secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data, $secureRenderer);
    }

    /**
     * Render choices.
     *
     * @param array $option
     * @param string[] $selected
     * @return string
     */
    protected function _optionToHtml($option, $selected)
    {
        $media = '';
        if (is_array($option) && isset($option['visual'])) {
            $media = '<div class="media" onclick="jQuery(this).parent().find(\'input:not(:disabled)\').prop( \'checked\', true).trigger(\'click\')">'
                . $option['visual']
                . '</div>';
        }
        $html = '<div class="admin__field admin__field-option">' . $media .
            '<input type="radio"' . $this->getRadioButtonAttributes($option);
        if (is_array($option)) {
            $option = new DataObject($option);
            $optionId = $this->getHtmlId() . $option['value'];
            $html .= 'value="' . $this->_escape(
                    $option['value']
                ) . '" class="admin__control-radio" id="' .$optionId  .'"';
            if ($option['value'] == $selected) {
                $html .= ' checked="checked"';
            }
            $html .= ' />';
            $html .= '<label class="admin__field-label" for="' .
                $this->getHtmlId() .
                $option['value'] .
                '"><span>' .
                $option['label'] .
                '</span></label>';
        } elseif ($option instanceof DataObject) {
            $optionId = $this->getHtmlId() . $option->getValue();
            $html .= 'id="' .$optionId  .'"' .$option->serialize(
                    ['label', 'title', 'value', 'class']
                );
            if (in_array($option->getValue(), $selected)) {
                $html .= ' checked="checked"';
            }
            $html .= ' />';
            $html .= '<label class="inline" for="' .
                $this->getHtmlId() .
                $option->getValue() .
                '">' .
                $option->getLabel() .
                '</label>';
        }

        if ($option->getStyle()) {
            $html .= $this->secureRenderer->renderStyleAsTag($option->getStyle(), "#$optionId");
        }
        if ($option->getOnclick()) {
            $this->secureRenderer->renderEventListenerAsTag('onclick', $option->getOnclick(), "#$optionId");
        }
        if ($option->getOnchange()) {
            $this->secureRenderer->renderEventListenerAsTag('onchange', $option->getOnchange(), "#$optionId");
        }
        $html .= '</div>';

        return $html;
    }
}
