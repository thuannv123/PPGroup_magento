<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation GraphQl for Magento 2 (System)
 */

/* Delete attribute  with amshop_dropdown_attribute code */

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\TestFramework\Helper\Bootstrap;

$registry = Bootstrap::getObjectManager()->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $attribute Attribute */
$attribute = Bootstrap::getObjectManager()->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute');

$attribute->load('amshop_dropdown_attribute', 'attribute_code');
$attribute->delete();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
