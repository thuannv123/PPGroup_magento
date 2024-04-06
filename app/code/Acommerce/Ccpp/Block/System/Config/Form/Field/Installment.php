<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 * PHP version 5
 *
 * @category Acommerce_Ccpp
 * @package  Acommerce
 * @author   Ranai L <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */

namespace Acommerce\Ccpp\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Ccpp payment field array
 *
 * @category Acommerce_Ccpp
 * @package  Acommerce
 * @author   Ranai L <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */
class Installment extends AbstractFieldArray
{

    // @codingStandardsIgnoreStart
    /**
     * Grid columns
     *
     * @var array
     */
    protected $_columns = array();

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
     * Check if columns are defined, set template
     *
     * @return void
     */

    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = __('Add');

    }//end _construct()


    /**
     * Prepare To Render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('code', array(
                'label' => __('Code'), 'class' => 'required-entry validate-number'
            )
        );

        $this->addColumn('description', array(
                'label' => __('Description'),
                'class' => 'required-entry'
            )
        );
        $this->addColumn('min_amount', array(
                'label' => __('Minimun Amount'),
                'class' => 'required-entry validate-number'
            )
        );
        $this->addColumn('max_amount', array(
                'label' => __('Maximum Amount'),
                'class' => 'required-entry validate-number'
            )
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');

    }//end _prepareToRender()
    // @codingStandardsIgnoreEnd
}//end class
