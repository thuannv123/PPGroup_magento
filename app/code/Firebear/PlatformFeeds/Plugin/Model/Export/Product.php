<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Plugin\Model\Export;

use Firebear\PlatformFeeds\Model\Export\DataProvider\Registry;
use Firebear\ImportExport\Model\Export\Product as ImportExportProduct;
use Firebear\PlatformFeeds\Model\Export\Adapter\Product as FeedsWriter;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Magento\Framework\Exception\LocalizedException;

class Product
{
    /**
     * Count of the items when preview mode is enabled
     *
     * @var int
     */
    const COUNT_ITEMS_PREVIEW = 10;

    /**
     * Count of items fetched when attributes requested
     *
     * @var int
     */
    const COUNT_ITEMS_ATTRIBUTE_REQUEST = 1;

    /**
     * Default parameters
     *
     * @var array
     */
    const DEFAULT_PARAMETERS = [
        // Doesn't matter
        'job_id' => 0,
        'entity' => 'catalog_product',
        'file_format' => 'feeds_product',
        'list' => [],
        'export_filter_table' => [],
        'replace_code' => [],
        'replace_value' => [],
        'behavior_data' => [
            'export_by_page' => 0,
            'file_format' => 'feeds_product',
            'store_ids' => [0],
            'google_sheet_auth_config' => '',
            'feed_template' => ''
        ],
        'all_fields' => 0,
        'dependencies' => [],
        'language' => 'en_US',
        'divided_additional' => 0,
        'only_admin' => 0,
        'xslt' => '',
        'xml_switch' => 0,
        'enable_last_entity_id' => 0,
        'last_entity_id' => 0,
    ];

    /**
     * Added support of preview mode and handle the fake export request to fetch attributes
     *
     * @param ImportExportProduct $subject
     * @param callable $proceed
     * @return array
     */
    public function aroundExport(ImportExportProduct $subject, callable $proceed)
    {
        $writer = Registry::getInstance()->getRowDataWriter();
        if ($writer) {
            return $this->fakeExportToGetAttributeNames($writer, $subject);
        }

        if (Registry::getInstance()->isPreviewMode()) {
            $subject->setTotalEntitiesLimit(self::COUNT_ITEMS_PREVIEW);
        }

        return $proceed();
    }

    /**
     * Get export fields in rowData
     *
     * @see ImportExportProduct::export()
     * @param FeedsWriter $writer
     * @param ImportExportProduct $productExport
     * @return array
     */
    protected function fakeExportToGetAttributeNames($writer, $productExport)
    {
        $this->initializeRowFieldsGetter($writer, $productExport);

        $exportData = $productExport->getExportData();
        if (empty($exportData)) {
            return [];
        }

        $rowData = $productExport->_customFieldsMapping($exportData[0]);
        return $rowData;
    }

    /**
     * Init product export model with default settings
     *
     * @param FeedsWriter $writer
     * @param ImportExportProduct $productExport
     */
    protected function initializeRowFieldsGetter($writer, $productExport)
    {
        $productExport->setWriter($writer);
        $productExport->setParameters(self::DEFAULT_PARAMETERS);

        /** @var AbstractCollection $entityCollection */
        $entityCollection = $productExport->_getEntityCollection(true);
        $entityCollection->setStoreId(Store::DEFAULT_STORE_ID);

        $productExport->_prepareEntityCollection($entityCollection);
        $productExport->paginateCollection(1, self::COUNT_ITEMS_ATTRIBUTE_REQUEST);
    }

    /**
     * Merge rows with the same sku into one by making data set containing 1 row per sku code
     *
     * @param ImportExportProduct $subject
     * @param ImportExportProduct $exportData
     * @return array
     * @throws LocalizedException
     */
    public function afterGetExportData(ImportExportProduct $subject, $exportData)
    {
        $writer = $subject->getWriter();
        if ($writer instanceof FeedsWriter) {
            $uniqueRows = [];
            foreach ($exportData as $row) {
                if (!empty($uniqueRows[$row['sku']])) {
                    $uniqueRows[$row['sku']] = array_merge($uniqueRows[$row['sku']], $row);
                } else {
                    $uniqueRows[$row['sku']] = $row;
                }
            }

            $exportData = array_values($uniqueRows);
        }

        return $exportData;
    }
}
