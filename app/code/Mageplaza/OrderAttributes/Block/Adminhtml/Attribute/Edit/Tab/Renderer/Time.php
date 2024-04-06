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

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Date;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mageplaza\OrderAttributes\Helper\Data;

/**
 * Class Time
 * @package Mageplaza\OrderAttributes\Block\Adminhtml\Attribute\Edit\Tab\Renderer
 */
class Time extends Date
{
    /**
     * @var \DateTime
     */
    protected $_value;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param TimezoneInterface $localeDate
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        TimezoneInterface $localeDate,
        Data $dataHelper,
        $data = []
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $localeDate, $data);
    }

    /**
     * Set date value
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        if (empty($value)) {
            $this->_value = '';

            return $this;
        }

        if ($value instanceof \DateTimeInterface) {
            $this->_value = $value;

            return $this;
        }

        try {
            if (preg_match('/^[0-9]+$/', $value)) {
                $this->_value = (new DateTime())->setTimestamp($this->_toTimestamp($value));
            } elseif (is_string($value)) {
                $this->_value = new DateTime($value, new DateTimeZone($this->localeDate->getConfigTimezone()));
            } else {
                $this->_value = '';
            }
        } catch (Exception $e) {
            $this->_value = '';
        }

        return $this;
    }

    /**
     * Get date value as string.
     *
     * Format can be specified, or it will be taken from $this->getFormat()
     *
     * @param string $format (compatible with \DateTime)
     *
     * @return string
     */
    public function getValue($format = null)
    {
        if (empty($this->_value)) {
            return '';
        }
        if (null === $format) {
            $format = $this->getDateFormat();
            $format .= ($format && $this->getTimeFormat()) ? ' ' : '';
            $format .= $this->getTimeFormat() ? $this->getTimeFormat() : '';
        }

        return $this->localeDate->formatDateTime(
            $this->_value,
            null,
            null,
            null,
            $this->_value->getTimezone(),
            $format
        );
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getElementHtml()
    {
        $this->addClass('admin__control-text input-text input-date');
        $timeFormat = $this->getTimeFormat();

        $dataInit = 'data-mage-init="' .
            $this->_escape(
                Data::jsonEncode(
                    [
                        'calendar' => [
                            'timeOnly'    => true,
                            'showsTime'   => !empty($timeFormat),
                            'timeFormat'  => $timeFormat,
                            'buttonImage' => $this->getImage(),
                            'buttonText'  => 'Select Date',
                            'disabled'    => $this->getDisabled(),
                            'minDate'     => $this->getMinDate(),
                            'maxDate'     => $this->getMaxDate(),
                        ],
                    ]
                )
            ) . '"';

        $html = sprintf(
            '<input name="%s" id="%s" value="%s" %s %s />',
            $this->getName(),
            $this->getHtmlId(),
            $this->_escape($this->getValue()),
            $this->serialize($this->getHtmlAttributes()),
            $dataInit
        );
        $html .= $this->getAfterElementHtml();

        return $html;
    }
}
