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
use Magento\Framework\Data\Form\Element\Checkboxes as ElemCheckboxes;
use Magento\Framework\Escaper;

/**
 * Class Checkboxes
 * @package Mageplaza\OrderAttributes\Block\Adminhtml\Attribute\Edit\Tab\Renderer
 */
class Checkboxes extends ElemCheckboxes
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * Prepare value list
     *
     * @return array
     */
    protected function _prepareValues()
    {
        $options = [];
        $values = [];

        if ($this->getValues()) {
            if (!is_array($this->getValues())) {
                $options = [$this->getValues()];
            } else {
                $options = $this->getValues();
            }
        } elseif ($this->getOptions() && is_array($this->getOptions())) {
            $options = $this->getOptions();
        }
        foreach ($options as $k => $v) {
            if (is_array($v)) {
                if (isset($v['value'])) {
                    if (!isset($v['label'])) {
                        $v['label'] = $v['value'];
                    }
                    if (isset($v['visual'])) {
                        $values[] = ['label' => $v['label'], 'value' => $v['value'], 'visual' => $v['visual']];
                    } else {
                        $values[] = ['label' => $v['label'], 'value' => $v['value']];
                    }
                }
            } else {
                $values[] = ['label' => $v, 'value' => $k];
            }
        }

        return $values;
    }

    /**
     * Was given value selected?
     *
     * @param string $value
     * @return string|null
     */
    public function getChecked($value)
    {
        $checked = $this->getValue() ?? $this->getData('checked');
        if (!$checked) {
            return null;
        }
        if (!is_array($checked)) {
            $checked = explode(',', $checked);
        } else {
            foreach ($checked as $k => $v) {
                $checked[$k] = (string)$v;
            }
        }
        if (in_array((string)$value, $checked)) {
            return 'checked';
        }
        return null;
    }

    /**
     * Render a checkbox.
     *
     * @param array $option
     * @return string
     */
    protected function _optionToHtml($option)
    {
        $id = $this->getHtmlId() . '_' . $this->_escape($option['value']);
        $media = '';
        if (is_array($option) && isset($option['visual'])) {
            $media = '<div class="media" onclick="jQuery(this).parent().find(\'input:not(:disabled)\').prop( \'checked\', true).trigger(\'click\')">'
                . $option['visual']
                . '</div>';
        }

        $html = '<div class="field choice admin__field admin__field-option">'.$media.'<input id="' . $id . '"';
        foreach ($this->getHtmlAttributes() as $attribute) {
            if ($value = $this->getDataUsingMethod($attribute, $option['value'])) {
                $html .= ' ' . $attribute . '="' . $value . '" class="admin__control-checkbox"';
            }
        }
        $html .= ' value="' .
            $option['value'] .
            '" />' .
            ' <label for="' .
            $id .
            '" class="admin__field-label"><span>' .
            $option['label'] .
            '</span></label></div>' .
            "\n";
        return $html;
    }

    /**
     * @return string
     */
    public function getName() {
        return parent::getName() . '[]';
    }
}
