<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Helper\Api;

use Firebear\PlatformFeeds\Helper\Data;
use Firebear\PlatformFeeds\Vendor\SpreadsheetReader as Reader;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Filesystem\DirectoryList;

class Yandex extends AbstractApi
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @inheritdoc
     * @param Reader $reader
     * @param DirectoryList $directoryList
     */
    public function __construct(Reader $reader, DirectoryList $directoryList, Context $context, Data $helper)
    {
        $this->reader = $reader;
        $this->directoryList = $directoryList;

        parent::__construct($context, $helper);
    }

    /**
     * @inheritdoc
     */
    public function getCategory($formData, $id, $typeId)
    {
        $categories = $this->helper->getCategoriesCache("feed_categories_{$typeId}_{$id}");
        if (!$categories) {
            $response = $this->getResponse();
            if (!$response) {
                return $categories;
            }

            if ($response) {
                $this->saveToFile($response);
                $categories = $this->prepareCategory();
                if ($categories) {
                    $this->helper->categoriesCacheSave($categories, "feed_categories_{$typeId}_{$id}");
                }
            }
        }

        return $categories;
    }

    /**
     * Save file with yandex categories
     *
     * @param string $response
     */
    protected function saveToFile($response)
    {
        $saveTo = $this->getFilePath();
        file_put_contents($saveTo, $response);
    }

    /**
     * Load feed google category
     *
     * @return array
     */
    public function prepareCategory()
    {
        $categories = [];
        $path = $this->getFilePath();

        try {
            $this->reader->read($path);
        } catch (\Throwable $throwable) {
            return $categories;
        }

        $rowsCount = $this->reader->rowcount();
        if (empty($rowsCount)) {
            return $categories;
        }

        for ($i = 1; $i <= $rowsCount; $i++) {
            $categories[] = $this->reader->val($i, 1);
        }

        return $categories;
    }

    /**
     * @return bool|string
     */
    public function getResponse()
    {
        $url = $this->getUrl();
        $response = $this->sendRequest($url);
        if (empty($response)) {
            $response = file_get_contents($url);
            if (empty($response)) {
                $response = false;
            }
        }

        return $response;
    }

    /**
     * @param $url
     * @return bool|string
     */
    public function sendRequest($url)
    {
        $curlHandler = curl_init($url);
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curlHandler);
        curl_close($curlHandler);

        return $response;
    }

    /**
     * @return string
     */
    protected function getUrl()
    {
        return 'http://download.cdn.yandex.net/market/market_categories.xls';
    }

    /**
     * @return string
     */
    protected function getFilePath()
    {
        return $this->directoryList->getPath('var') . 'yandex_market_categories.xls';
    }
}
