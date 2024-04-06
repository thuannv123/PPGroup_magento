<?php
namespace PPGroup\Integration\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class ShippingMapping extends AbstractFieldArray
{
    /**
     * Grid columns
     *
     * @var array
     */
    protected $_columns = array();

    /**
     * Grid columns
     *
     * @var array
     */
    protected $_shippingMethodRenderer;

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
     * Check if columns are defined, set template
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
     * @param string $name   Column Name
     * @param array  $params Params
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
     * Returns renderer for Shipping element
     *
     * @return \Magento\Braintree\Block\Adminhtml\Form\Field\Countries
     */
    protected function getShippingRenderer()
    {
        if (!$this->_shippingMethodRenderer) {
            $this->_shippingMethodRenderer = $this->getLayout()->createBlock(
                'PPGroup\Integration\Block\Adminhtml\System\Config\Form\Field\Render\Shippings', '', ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_shippingMethodRenderer;
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
        $shippingMethod = $row->getShippingMethod();
        $options = [];
        if ($shippingMethod) {
            $options['option_' . $this->getShippingRenderer()->calcOptionHash($shippingMethod)] = 'selected="selected"';
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
            'shipping_method', [
                'label' => __('Shipping Method'),
                //'renderer' => $this->getShippingRenderer(),
                ]
        );

        $this->addColumn('value', array('label' => __('Value')));

        $this->_addAfter       = false;
        $this->_addButtonLabel = __('Add');

    }
}
