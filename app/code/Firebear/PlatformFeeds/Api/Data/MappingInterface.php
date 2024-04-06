<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Api\Data;

interface MappingInterface
{
    const ID = 'id';
    const TITLE = 'title';
    const TYPE_ID = 'type_id';
    const MAPPING_DATA = 'mapping_data';
    const CREDENTIALS_DATA = 'credentials_data';
    const TABLE_NAME = 'firebear_feed_category_mapping';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return int
     */
    public function getTypeId();

    /**
     * @return string|null
     */
    public function getCredentialsData();

    /**
     * @return string
     */
    public function getMappingData();

    /**
     * @param $mappingId
     * @return MappingInterface
     */
    public function setId($mappingId);

    /**
     * @param $title
     * @return MappingInterface
     */
    public function setTitle($title);

    /**
     * @param $feedId
     * @return MappingInterface
     */
    public function setTypeId($feedId);

    /**
     * @param $credentials
     * @return MappingInterface
     */
    public function setCredentialsData($credentials);

    /**
     * @param $mapping
     * @return MappingInterface
     */
    public function setMappingData($mapping);
}
