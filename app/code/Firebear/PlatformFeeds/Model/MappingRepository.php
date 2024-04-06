<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio GmbH. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Firebear\PlatformFeeds\Api\MappingRepositoryInterface;
use Firebear\PlatformFeeds\Model\ResourceModel\Mapping as MappingResource;
use Firebear\PlatformFeeds\Model\MappingFactory;
use Firebear\PlatformFeeds\Model\ResourceModel\Mapping\CollectionFactory as MappingCollectionFactory;

class MappingRepository implements MappingRepositoryInterface
{
    /**
     * @var MappingResource
     */
    protected $resource;

    /**
     * @var \Firebear\PlatformFeeds\Model\MappingFactory
     */
    protected $mappingFactory;

    /**
     * @var ExportCollectionFactory
     */
    protected $mappingCollectionFactory;

    /**
     * MappingRepository constructor.
     * @param MappingResource $resource
     * @param \Firebear\PlatformFeeds\Model\MappingFactory $mappingFactory
     * @param MappingCollectionFactory $mappingCollectionFactory
     */
    public function __construct(
        MappingResource $resource,
        MappingFactory $mappingFactory,
        MappingCollectionFactory $mappingCollectionFactory
    ) {
        $this->resource                 = $resource;
        $this->mappingFactory           = $mappingFactory;
        $this->mappingCollectionFactory = $mappingCollectionFactory;
    }

    /**
     * @param \Firebear\PlatformFeeds\Api\Data\MappingInterface $mapping
     * @return \Firebear\PlatformFeeds\Api\Data\MappingInterface
     * @throws CouldNotSaveException
     */
    public function save(\Firebear\PlatformFeeds\Api\Data\MappingInterface $mapping)
    {
        try {
            $this->resource->save($mapping);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the mapping: %1',
                    $exception->getMessage()
                )
            );
        }

        return $mapping;
    }

    /**
     * @param $mappingId
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($mappingId)
    {
        $mapping = $this->mappingFactory->create();
        $this->resource->load($mapping, $mappingId);
        if (!$mapping->getId()) {
            throw new NoSuchEntityException(__('Mapping with id "%1" does not exist.', $mappingId));
        }

        return $mapping;
    }

    /**
     * @param \Firebear\PlatformFeeds\Api\Data\MappingInterface $mapping
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Firebear\PlatformFeeds\Api\Data\MappingInterface $mapping)
    {
        try {
            $this->resource->delete($mapping);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the mapping: %1',
                    $exception->getMessage()
                )
            );
        }

        return true;
    }

    /**
     * @param int $mappingId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($mappingId)
    {
        return $this->delete($this->getById($mappingId));
    }
}
