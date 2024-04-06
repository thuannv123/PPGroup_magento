<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Model;

use Magento\Framework\Model\AbstractModel;
use Firebear\PlatformFeeds\Api\Data\MappingInterface;
use Firebear\PlatformFeeds\Model\ResourceModel\Mapping as MappingResource;

class Mapping extends AbstractModel implements MappingInterface
{
    /**
     * @inheritdoc
     */
    public function _construct()
    {
        $this->_init(MappingResource::class);
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @return int
     */
    public function getTypeId()
    {
        return $this->getData(self::TYPE_ID);
    }

    /**
     * @return string|null
     */
    public function getCredentialsData()
    {
        return $this->getData(self::CREDENTIALS_DATA);
    }

    /**
     * @return string|null
     */
    public function getMappingData()
    {
        return $this->getData(self::MAPPING_DATA);
    }

    /**
     * @param int $mappingId
     * @return Mapping
     */
    public function setId($mappingId)
    {
        return $this->setData(self::ID, $mappingId);
    }

    /**
     * @param string $title
     * @return Mapping
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @param int $feedId
     * @return Mapping
     */
    public function setTypeId($feedId)
    {
        return $this->setData(self::TYPE_ID, $feedId);
    }

    /**
     * @param string|null $credentials
     * @return Mapping
     */
    public function setCredentialsData($credentials)
    {
        return $this->setData(self::CREDENTIALS_DATA, $credentials);
    }

    /**
     * @param string|null $source
     * @return Mapping
     */
    public function setMappingData($source)
    {
        return $this->setData(self::MAPPING_DATA, $source);
    }
}
