<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface MappingRepositoryInterface
{
    /**
     * Save mapping.
     *
     * @param Data\MappingInterface $mapping
     * @return Data\MappingInterface
     * @throws LocalizedException
     */
    public function save(Data\MappingInterface $mapping);

    /**
     * Retrieve mapping.
     *
     * @param int $mappingId
     * @return Data\MappingInterface
     * @throws LocalizedException
     */
    public function getById($mappingId);

    /**
     * Delete mapping.
     *
     * @param Data\MappingInterface $mapping
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(Data\MappingInterface $mapping);

    /**
     * Delete mapping by ID.
     *
     * @param int $mappingId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($mappingId);
}
