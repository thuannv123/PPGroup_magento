<?php

namespace PPGroup\Integration\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class PaymentMapping extends AbstractFieldArray
{
    /**
     * Grid columns
     *
     * @var array
     */
    protected $_columns = array();

    /**
     * Payment Method Renderer
     *
     * @var DataObject
     */
    protected $_paymentMethodRenderer;

    /**
     * Order Status Renderer
     *
     * @var DataObject
     */
    protected $_orderStatusRenderer;

    /**
     * Enable the "Add after" button or not
     *
     * @var bool
     */
    protected $_addAfter = true;

    /**
     * Label of add button
     *
     * @var string
     */
    protected $_addButtonLabel;

    /**
     * Template
     *
     * @var string
     */
    protected $_template
        = 'PPGroup_Integration::system/config/form/field/array.phtml';

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = __('Add');

    }

    /**
     * Add a column to array-grid
     *
     * @param string $name Column Name
     * @param array $params Params
     *
     * @return void
     */
    public function addColumn($name, $params)
    {
        $this->_columns[$name] = [
            'label' => $this->_getParam($params, 'label', 'Column'),
            'size' => $this->_getParam($params, 'size', false),
            'style' => $this->_getParam($params, 'style'),
            'class' => $this->_getParam($params, 'class'),
            'renderer' => false,
        ];
        if (!empty($params['renderer'])
            && $params['renderer'] instanceof \Magento\Framework\View\Element\AbstractBlock
        ) {
            $this->_columns[$name]['renderer'] = $params['renderer'];
        }
    }

    /**
     * Returns renderer for payment element
     *
     * @return \Magento\Braintree\Block\Adminhtml\Form\Field\Countries
     */
    protected function getPaymentRenderer()
    {
        if (!$this->_paymentMethodRenderer) {
            $this->_paymentMethodRenderer = $this->getLayout()->createBlock(
                'PPGroup\Integration\Block\Adminhtml\System\Config\Form\Field\Render\Payments',
                '', ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_paymentMethodRenderer;
    }

    /**
     * Prepare Array Row
     *
     * @param DataObject $row Data Row
     *
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $paymentMethod = $row->getPaymentMethod();
        $options = [];
        if ($paymentMethod) {
            $options['option_' . $this->getPaymentRenderer()
                ->calcOptionHash($paymentMethod)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);

    }

    /**
     * Prepare To Render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'payment_method', [
                'label' => __('Payment Method'),
                'renderer' => $this->getPaymentRenderer(),
            ]
        );

        $this->addColumn('value', array('label' => __('Value')));

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');

    }
}
