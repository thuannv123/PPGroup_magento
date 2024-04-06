<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */
declare(strict_types=1);

namespace Firebear\PlatformFeeds\Plugin\Controller\Adminhtml\Export\Job;

use Firebear\ImportExport\Controller\Adminhtml\Export\Job\Save as SaveAction;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Save
{
    /**
     * @var string
     */
    const SOURCE_CATEGORY = 'source_category';

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ResourceInterface
     */
    protected $moduleResource;

    /**
     * Save constructor.
     *
     * @param SerializerInterface $serializer
     * @param ResourceInterface $moduleResource
     */
    public function __construct(
        SerializerInterface $serializer,
        ResourceInterface $moduleResource
    ) {
        $this->serializer = $serializer;
        $this->moduleResource = $moduleResource;
    }

    /**
     * Controller after prepareData() plugin
     * Save the category mapping data
     *
     * @param SaveAction $subject
     * @param array $data
     * @return array $result
     * @see SaveAction::prepareData($data)
     */
    public function afterPrepareData(
        SaveAction $subject,
        $data
    ) {
        $sourceCategory = $subject->searchFields($data, self::SOURCE_CATEGORY . '_');
        if (!empty($sourceCategory) && !empty($data['source_data'])) {
            $sourceData = $data['source_data'];
            if (!is_array($sourceData)) {
                $sourceData = $this->serializer->unserialize($data['source_data']);
            }
            $sourceData += $sourceCategory;
            $importExportVersion = $this->moduleResource->getDbVersion('Firebear_ImportExport');
            if (version_compare($importExportVersion, '3.5', '<=')) {
                $sourceData = $this->serializer->serialize($sourceData);
            }
            $data['source_data'] = $sourceData;
        }

        return $data;
    }
}
