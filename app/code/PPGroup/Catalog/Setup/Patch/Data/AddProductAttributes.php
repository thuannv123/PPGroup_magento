<?php

namespace PPGroup\Catalog\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class AddSwatchImageAttribute
 * @package Magento\Swatches\Setup\Patch
 */
class AddProductAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        /**
         * Install eav entity types to the eav/entity_type table
         */
        $eavSetup->addAttribute(
            'catalog_product',
            'brand',
            [
                'type' => 'int',
                'label' => 'Brand',
                'input' => 'select',
                'required' => 1,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'is_unique' => 0,
                'is_used_in_grid' => 1,
                'is_visible_in_grid' => 1,
                'is_filterable_in_grid' => 1,
                'user_defined' => 1,
             ]
        );

        $eavSetup->addAttribute(
            'catalog_product',
            'size',
            [
                'type' => 'int',
                'label' => 'Size',
                'input' => 'swatch_text',
                'required' => 0,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'is_unique' => 0,
                'is_used_in_grid' => 0,
                'is_visible_in_grid' => 0,
                'is_filterable_in_grid' => 0,
                'user_defined' => 1,
            ]
        );

        $eavSetup->addAttribute(
            'catalog_product',
            'gender',
            [
                'type' => 'int',
                'label' => 'Gender',
                'input' => 'select',
                'required' => 0,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'is_unique' => 0,
                'is_used_in_grid' => 0,
                'is_visible_in_grid' => 0,
                'is_filterable_in_grid' => 0,
                'user_defined' => 1,
            ]
        );

        $eavSetup->addAttribute(
            'catalog_product',
            'brand_collection',
            [
                'type' => 'int',
                'label' => 'Collection',
                'input' => 'select',
                'required' => 0,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'is_unique' => 0,
                'is_used_in_grid' => 0,
                'is_visible_in_grid' => 0,
                'is_filterable_in_grid' => 0,
                'user_defined' => 1,
            ]
        );

        $eavSetup->addAttribute(
            'catalog_product',
            'is_new',
            [
                'type' => 'int',
                'label' => 'New',
                'input' => 'boolean',
                'required' => 0,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'is_unique' => 0,
                'is_used_in_grid' => 0,
                'is_visible_in_grid' => 0,
                'is_filterable_in_grid' => 0,
                'user_defined' => 1,
            ]
        );

        $eavSetup->addAttribute(
            'catalog_product',
            'is_sale',
            [
                'type' => 'int',
                'label' => 'Sale',
                'input' => 'boolean',
                'required' => 0,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'is_unique' => 0,
                'is_used_in_grid' => 0,
                'is_visible_in_grid' => 0,
                'is_filterable_in_grid' => 0,
                'user_defined' => 1,
            ]
        );
    }
}
