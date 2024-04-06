<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */
declare(strict_types=1);

namespace Firebear\PlatformFeeds\Plugin\Model\ExportJob;

use Firebear\ImportExport\Model\ExportJob\Processor as ExportJobProcessor;
use Firebear\PlatformFeeds\Model\Export\DataProvider\Registry;
use Magento\Framework\Serialize\SerializerInterface;

class Processor
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Processor constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Save parameter before process it in afterGetMapData
     *
     * @param ExportJobProcessor $subject
     * @param array $mapData
     * @return null
     */
    public function beforeGetMapData(ExportJobProcessor $subject, $mapData)
    {
        if (is_string($mapData)) {
            $mapData = $this->serializer->unserialize($mapData);
        }

        if (!empty($mapData[Registry::DATA_KEY_SOURCE_CATEGORY])) {
            Registry::getInstance()->setSourceCategoryMapData($mapData[Registry::DATA_KEY_SOURCE_CATEGORY]);
        }

        if ($mapData[Registry::DATA_KEY_SOURCE_CATEGORY_FEED_MAPPING_ID] ?? false) {
            Registry::getInstance()->setMappingId($mapData[Registry::DATA_KEY_SOURCE_CATEGORY_FEED_MAPPING_ID]);
        }
        return null;
    }
}
