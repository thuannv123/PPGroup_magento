<?php

/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PPGroup\ConfigurableProduct\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Helper\Product\Options\Loader;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;

/**
 * Repository for performing CRUD operations for a configurable product's options.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionRepositoryOverride extends \Magento\ConfigurableProduct\Model\OptionRepository
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory
     */
    protected $optionValueFactory;

    /**
     * @var Product\Type\Configurable
     */
    protected $configurableType;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute
     */
    protected $optionResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * @var ConfigurableType\AttributeFactory
     */
    protected $configurableAttributeFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    private $configurableTypeResource;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Loader
     */
    private $optionLoader;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory $optionValueFactory
     * @param ConfigurableType $configurableType
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute $optionResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param ConfigurableType\AttributeFactory $configurableAttributeFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableTypeResource
     * @param Loader $optionLoader
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory $optionValueFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute $optionResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory $configurableAttributeFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableTypeResource,
        Loader $optionLoader
    ) {
        // $this->productRepository = $productRepository;
        // $this->optionValueFactory = $optionValueFactory;
        // $this->configurableType = $configurableType;
        // $this->optionResource = $optionResource;
        // $this->storeManager = $storeManager;
        // $this->productAttributeRepository = $productAttributeRepository;
        // $this->configurableAttributeFactory = $configurableAttributeFactory;
        // $this->configurableTypeResource = $configurableTypeResource;
        // $this->optionLoader = $optionLoader;
        parent::__construct($productRepository, $optionValueFactory, $configurableType, $optionResource, $storeManager, 
        $productAttributeRepository, $configurableAttributeFactory,$configurableTypeResource , $optionLoader);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function save($sku, OptionInterface $option)
    {
        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        if ($option->getId()) {
            /** @var Product $product */
            $product = $this->getProduct($sku);
            $data = $option->getData();
            $option->load($option->getId());
            $option->setData(array_replace_recursive($option->getData(), $data));
            if (!$option->getId() || $option->getProductId() != $product->getData($metadata->getLinkField())) {
                throw new NoSuchEntityException(
                    __(
                        'Option with id "%1" not found',
                        $option->getId()
                    )
                );
            }
        } else {
            /** @var Product $product */
            $product = $this->productRepository->get($sku);
            $this->validateNewOptionData($option);
            $allowedTypes = [ProductType::TYPE_SIMPLE, ProductType::TYPE_VIRTUAL, ConfigurableType::TYPE_CODE];
            if (!in_array($product->getTypeId(), $allowedTypes)) {
                throw new \InvalidArgumentException('Incompatible product type');
            }

            $linkFiled = ($metadata->getLinkField() == "row_id") ? "row_id" : $metadata->getLinkField();
            $option->setProductId($product->getData($linkFiled));
            if (!empty($option->getProductId() && !empty($option->getAttributeId()))) {
                $id = $this->optionResource->getIdByProductIdAndAttributeId(
                    $option,
                    $option->getProductId(),
                    $option->getAttributeId()
                );
                if (!empty($id)) {
                    $option->setId($id);
                }
            }
        }

        try {
            if ($product->getTypeId() === ProductType::TYPE_SIMPLE) {
                $product->setTypeId(ConfigurableType::TYPE_CODE);
                $product->save();
            }
            $option->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('An error occurred while saving the option. Please try to save again.'));
        }

        if (!$option->getId()) {
            throw new CouldNotSaveException(__('An error occurred while saving the option. Please try to save again.'));
        }
        return $option->getId();
    }

    /**
     * Retrieve product instance by sku
     *
     * @param string $sku
     * @return ProductInterface
     * @throws InputException
     */
    private function getProduct($sku)
    {
        $product = $this->productRepository->get($sku);
        if (\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE !== $product->getTypeId()) {
            throw new InputException(
                __('This is implemented for the "%1" configurable product only.', $sku)
            );
        }
        return $product;
    }

    /**
     * Retrieve product instance by id
     *
     * @param int $id
     * @return ProductInterface
     * @throws InputException
     */
    private function getProductById($id)
    {
        $product = $this->productRepository->getById($id);
        if (\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE !== $product->getTypeId()) {
            throw new InputException(
                __('This is implemented for the "%1" configurable product only.', $id)
            );
        }
        return $product;
    }

    /**
     * Ensure that all necessary data is available for a new option creation.
     *
     * @param OptionInterface $option
     * @return void
     * @throws InputException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateNewOptionData(OptionInterface $option)
    {
        $inputException = new InputException();
        if (!$option->getAttributeId()) {
            $inputException->addError(__('Option attribute ID is not specified.'));
        }
        if (!$option->getLabel()) {
            $inputException->addError(__('Option label is not specified.'));
        }
        if (!$option->getValues()) {
            $inputException->addError(__('Option values are not specified.'));
        } else {
            foreach ($option->getValues() as $optionValue) {
                if (null === $optionValue->getValueIndex()) {
                    $inputException->addError(__('Value index is not specified for an option.'));
                }
            }
        }
        if ($inputException->wasErrorAdded()) {
            throw $inputException;
        }
    }

    /**
     * Get MetadataPool instance
     *
     * @return MetadataPool
     */
    private function getMetadataPool()
    {
        if (!$this->metadataPool) {
            $this->metadataPool = ObjectManager::getInstance()->get(MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
