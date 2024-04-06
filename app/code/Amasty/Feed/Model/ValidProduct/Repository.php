<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\ValidProduct;

use Amasty\Feed\Api\Data\ValidProductsInterface;
use Amasty\Feed\Model\ValidProduct\ResourceModel\Collection;
use Amasty\Feed\Model\ValidProduct\ResourceModel\CollectionFactory;
use Amasty\Feed\Model\ValidProduct\ResourceModel\ValidProduct as ValidProductResource;
use Amasty\Feed\Model\ValidProduct\ValidProductFactory;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;

class Repository implements \Amasty\Feed\Api\ValidProductsRepositoryInterface
{
    /**
     * @var ValidProductFactory
     */
    private $validProductsFactory;

    /**
     * @var ValidProductResource
     */
    private $validProductsResource;

    /**
     * @var \Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        ValidProductFactory $validProductsFactory,
        ValidProductResource $validProductsResource,
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->validProductsFactory = $validProductsFactory;
        $this->validProductsResource = $validProductsResource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ValidProductsInterface $validProducts)
    {
        try {
            $this->validProductsResource->save($validProducts);
        } catch (\Exception $e) {
            if ($validProducts->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save quote with ID %1. Error: %2',
                        [$validProducts->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new quote. Error: %1', $e->getMessage()));
        }

        return $validProducts;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Ui\Api\Data\BookmarkSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        /** @var Collection $validProductsCollection */
        $validProductsCollection = $this->collectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $validProductsCollection);
        }

        $searchResults->setTotalCount($validProductsCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $validProductsCollection);
        }

        $validProductsCollection->setCurPage($searchCriteria->getCurrentPage());
        $validProductsCollection->setPageSize($searchCriteria->getPageSize());
        $validProducts = [];

        /** @var ValidProductsInterface $validProduct */
        foreach ($validProductsCollection->getItems() as $validProduct) {
            $validProducts[] = $this->getById($validProduct->getEntityId())->getValidProductId();
        }

        $searchResults->setItems($validProducts);

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($entityId)
    {
        /** @var \Amasty\Feed\Model\ValidProduct\ValidProduct $validProducts */
        $validProducts = $this->validProductsFactory->create();
        $this->validProductsResource->load($validProducts, $entityId);
        if (!$validProducts->getEntityId()) {
            throw new NoSuchEntityException(__('Valid products with specified ID "%1" not found.', $entityId));
        }

        return $validProducts;
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $validProductsCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $validProductsCollection)
    {
        /** @var SortOrder \$sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $validProductsCollection->addOrder(
                $field,
                ($sortOrder->getDirection() === SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup \$filterGroup
     * @param Collection  $validProductsCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $validProductsCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $validProductsCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ValidProductsInterface $validProducts)
    {
        try {
            $this->validProductsResource->delete($validProducts);
        } catch (\Exception $e) {
            if ($validProducts->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove quote with ID %1. Error: %2',
                        [$validProducts->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove quote. Error: %1', $e->getMessage()));
        }

        return true;
    }
}
