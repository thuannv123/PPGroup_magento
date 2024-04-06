<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Helper\Api;

/**
 * @codingStandardsIgnoreFile
 * phpcs:ignoreFile -- file_get_contents is needed
 */
class Google extends AbstractApi
{
    /**
     * @inheritdoc
     */
    public function getCategory($formData, $id, $typeId)
    {
        $categories = $this->helper->getCategoriesCache("feed_categories_{$typeId}_{$id}");
        if (!$categories) {
            $response = $this->getResponse();
            $categories = $this->prepareCategory($response);
            if ($categories) {
                $this->helper->categoriesCacheSave($categories, "feed_categories_{$typeId}_{$id}");
            }
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
                $response = [];
            }
        }

        return $response;
    }

    /**
     * Load feed google category
     *
     * @param $response
     * @return array
     */
    public function prepareCategory($response)
    {
        $categories = [];
        $feedCategoriesList = explode(PHP_EOL, $response);
        foreach ($feedCategoriesList as $item) {
            $parts = explode(' - ', $item);
            if (!isset($parts[1])) {
                continue;
            }

            $categories[$parts[0]] = $parts[1];
        }

        return $categories;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return 'https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt';
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
}
