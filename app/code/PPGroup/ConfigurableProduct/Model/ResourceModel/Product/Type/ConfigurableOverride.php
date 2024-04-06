<?php
/**
 * Configurable product type resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PPGroup\ConfigurableProduct\Model\ResourceModel\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Catalog\Model\ResourceModel\Product\Relation as ProductRelation;
use Magento\Framework\Model\ResourceModel\Db\Context as DbContext;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\ConfigurableProduct\Model\AttributeOptionProviderInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Configurable product resource model.
 */
class ConfigurableOverride extends \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
{
    /**
     * Catalog product relation
     *
     * @var ProductRelation
     */
    protected $catalogProductRelation;

    /**
     * @var AttributeOptionProviderInterface
     */
    private $attributeOptionProvider;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var OptionProvider
     */
    private $optionProvider;

    /**
     * @param DbContext $context
     * @param ProductRelation $catalogProductRelation
     * @param string $connectionName
     * @param ScopeResolverInterface $scopeResolver
     * @param AttributeOptionProviderInterface $attributeOptionProvider
     * @param OptionProvider $optionProvider
     */
    public function __construct(
        DbContext $context,
        ProductRelation $catalogProductRelation,
        $connectionName = null,
        ScopeResolverInterface $scopeResolver = null,
        AttributeOptionProviderInterface $attributeOptionProvider = null,
        OptionProvider $optionProvider = null
    ) {
        $this->attributeOptionProvider = $attributeOptionProvider
            ?: ObjectManager::getInstance()->get(AttributeOptionProviderInterface::class);
        $this->optionProvider = $optionProvider ?: ObjectManager::getInstance()->get(OptionProvider::class);
        parent::__construct($context,$catalogProductRelation,$scopeResolver ,$connectionName);
    }



    /**
     * Save configurable product relations
     *
     * @param ProductModel $mainProduct the parent id
     * @param array $productIds the children id array
     * @return $this
     */
    public function saveProducts($mainProduct, array $productIds)
    {
        if (!$mainProduct instanceof ProductInterface) {
            return $this;
        }

        $linkFiedld = ($this->optionProvider->getProductEntityLinkField() == 'row_id')?'row_id':$this->optionProvider->getProductEntityLinkField();
        $productId = $mainProduct->getData( $linkFiedld ); 
        $select = $this->getConnection()->select()->from(
            ['t' => $this->getMainTable()],
            ['product_id']
        )->where(
            't.parent_id = ?',
            $productId
        );

        $existingProductIds = $this->getConnection()->fetchCol($select);
        $insertProductIds = array_diff($productIds, $existingProductIds);
        $deleteProductIds = array_diff($existingProductIds, $productIds);

        if (!empty($insertProductIds)) {
            $insertData = [];
            foreach ($insertProductIds as $id) {
                $insertData[] = ['product_id' => (int) $id, 'parent_id' => (int) $productId];
            }
            $this->getConnection()->insertMultiple(
                $this->getMainTable(),
                $insertData
            );
        }

        if (!empty($deleteProductIds)) {
            $where = ['parent_id = ?' => $productId, 'product_id IN (?)' => $deleteProductIds];
            $this->getConnection()->delete($this->getMainTable(), $where);
        }

        // configurable product relations should be added to relation table
        $this->catalogProductRelation->processRelations($productId, $productIds);

        return $this;
    }
}
