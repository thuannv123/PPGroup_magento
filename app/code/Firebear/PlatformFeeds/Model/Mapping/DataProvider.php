<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Model\Mapping;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Firebear\PlatformFeeds\Model\ResourceModel\Mapping\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $CollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $CollectionFactory,
        $meta = [],
        $data = []
    ) {
        $this->collection = $CollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        $this->loadedData = [];
        foreach ($items as $item) {
            $data = $item->getData();
            $data = $this->addCredentialsField($data);
            $mappingData = $this->addMappingField($data);
            $this->loadedData[$item->getId()] = $data;
            $this->loadedData[$item->getId()]['source_category_map'] = $mappingData;
        }

        return $this->loadedData;
    }

    public function addCredentialsField($data)
    {
        $credentialsData = $data['credentials_data'] ?? '';
        unset($data['credentials_data']);
        if ($credentialsData) {
            $credentialsData = json_decode($credentialsData, true);
            if (isset($credentialsData['token'])) {
                $data['token'] = $credentialsData['token'];
            }
            if (isset($credentialsData['login'])) {
                $data['login'] = $credentialsData['login'];
            }
            if (isset($credentialsData['password'])) {
                $data['password'] = $credentialsData['password'];
            }
        }

        return $data;
    }

    public function addMappingField($data)
    {
        $prepareMappData = [];
        $mappingData = $data['mapping_data'] ?? '';
        unset($data['mapping_data']);
        if ($mappingData) {
            $mappingData = json_decode($mappingData, true);
            foreach ($mappingData as $key => $mappData) {
                $prepareMappData[$key]['record_id'] = $mappData['record_id'];
                $prepareMappData[$key]['source_category_magento'] = $mappData['source_category_magento'];
                $prepareMappData[$key]['source_category_feed'] = $mappData['source_category_feed'];
            }
        }

        return $prepareMappData;
    }
}
