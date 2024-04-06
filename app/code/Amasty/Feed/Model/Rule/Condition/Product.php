<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Rule\Condition;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Ui\Component\Form\Element\Select;

class Product extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    private $productType;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    private $stockItemRepository;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        array $data
    ) {
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data
        );

        $this->productType = $productType;
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * @param AbstractModel $object
     *
     * @return array
     */
    public function getAvailableInCategories(AbstractModel $object)
    {
        $connection = $object->getResource()->getConnection();
        // is_parent=1 ensures that we'll get only category IDs those are direct parents of the product, instead of
        // fetching all parent IDs, including those are higher on the tree
        $select = $object->getResource()->getConnection()->select()->distinct()->from(
            $object->getResource()->getTable('catalog_category_product'),
            ['category_id']
        )->where(
            'product_id = ?',
            (int)$object->getEntityId()
        );

        return $connection->fetchCol($select);
    }

    public function validate(AbstractModel $model)
    {
        $attrCode = $this->getAttribute();

        switch ($attrCode) {
            case 'quantity_and_stock_status':
                try {
                    $stockItem = $this->stockItemRepository->get($model->getEntityId());

                    return $this->validateAttribute($stockItem->getData(StockItemInterface::IS_IN_STOCK));
                } catch (NoSuchEntityException $e) {
                    null;
                }
                break;
            case 'category_ids':
                $categories = $this->getAvailableInCategories($model);

                if ($this->getData('value') === '') {
                    $result = isset($categories[0]);

                    if ($this->getOperatorForValidate() == '{}') {
                        $result = !$result;
                    }
                } else {
                    $result = $this->validateAttribute($categories);
                }

                return $result;
        }

        if (!$model->hasData($attrCode)) {
            $productResource = $model->getResource();
            $attributeValue = $productResource->getAttributeRawValue(
                $model->getId(),
                $attrCode,
                $model->getStoreId()
            );

            if (is_array($attributeValue)) {
                $attributeValue = implode(',', $attributeValue);
            }

            $model->setData($attrCode, $attributeValue);
        }

        $oldAttrValue = $model->hasData($attrCode) ? $model->getData($attrCode) : null;
        $this->_setAttributeValue($model);
        $result = $this->validateAttribute($model->getData($this->getAttribute()));
        $this->_restoreOldAttrValue($model, $oldAttrValue);

        return (bool)$result;
    }

    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();
        $options = $this->getAttributeOption();
        $options['type_id'] = __('Type');
        // Override weird default attribute names
        $titles = [
            'status' => __('Status'),
            'quantity_and_stock_status' => __('Stock Status')
        ];

        foreach ($titles as $code => $title) {
            if (isset($options[$code])) {
                $options[$code] = $title;
            }
        }

        asort($options);
        $this->setAttributeOption($options);

        return $this;
    }

    public function getValueSelectOptions()
    {
        if ($this->getAttribute() === ProductInterface::TYPE_ID) {
            $this->setData('value_select_options', $this->productType->getOptions());
        }

        return parent::getValueSelectOptions();
    }

    public function getMappedSqlField()
    {
        if ($this->getAttribute() === ProductInterface::TYPE_ID) {
            return 'e.type_id';
        } else {
            return parent::getMappedSqlField();
        }
    }

    public function getAttributeObject(): Attribute
    {
        $object = parent::getAttributeObject();

        if ($this->getAttribute() === ProductInterface::TYPE_ID) {
            $object->setFrontendInput(Select::NAME);
        }

        return $object;
    }
}
