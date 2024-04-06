<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Helper\Api;

/**
 * @codingStandardsIgnoreFile
 * phpcs:ignoreFile -- curl_init is needed
 */
class Ebay extends AbstractApi
{
    /**
     * Get category
     *
     * @param array $formData
     * @param int $id
     * @param string $typeId
     * @return array|bool|float|int|mixed|string|null
     */
    public function getCategory($formData, $id, $typeId)
    {
        $categories = $this->helper->getCategoriesCache("feed_categories_{$typeId}_{$id}");

        if (!$categories) {
            $token = $formData['token'];
            $response = $this->getResponse($token);
            $response = json_decode(json_encode($response), true);
            $categories = $response['CategoryArray']['Category'] ?? false;

            if ($categories) {
                $categories = $this->prepareCategory($categories);
                $this->helper->categoriesCacheSave($categories, "feed_categories_{$typeId}_{$id}");
            }
        }

        return $categories;
    }

    /**
     * Prepare category data
     *
     * @param array $categories
     * @return array
     */
    public function prepareCategory($categories)
    {
        $prepareCategory = [];
        foreach ($categories as $category) {
            $catId = $category['CategoryID'];
            $catName = $category['CategoryName'];
            $catParentId = $category['CategoryParentID'];
            $catLevel = $category['CategoryLevel'];
            if ($catId == $catParentId) {
                $catParentId = 0;
            }

            $prepareCategory[$catId] = [
                'name' => $catName,
                'parentId' => $catParentId,
                'level' => $catLevel
            ];
        }

        $entities = [];
        foreach ($prepareCategory as $categoryId => $categoryData) {
            $categoryName = $this->getCategoryName($categoryId, $prepareCategory);
            if ($categoryName !== '') {
                $entities[$categoryId] = $categoryName;
            }
        }
        $categories = $entities;

        return $categories;
    }

    /**
     * Get category name
     *
     * @param int $id
     * @param array $data
     * @return string
     */
    public function getCategoryName($id, $data)
    {
        if (isset($data[$id]) && $data[$id]['parentId'] == '0') {
            return $data[$id]['name'];
        } elseif (isset($data[$id])) {
            $parentName = $this->getCategoryName($data[$id]['parentId'], $data);
            if ($parentName !== '') {
                return $parentName . '/' . $data[$id]['name'];
            }

            return $data[$id]['name'];
        }

        return '';
    }

    /**
     * @param $token
     * @return mixed|string
     */
    public function getResponse($token)
    {
        $request = $this->createRequest($token);
        $result = $this->sendRequest($request);

        if (isset($result->Ack) && $result->Ack == 'Success') {
            $response = $result;
        } else {
            $response = 'error';
        }

        return $response;
    }

    /**
     * @param $token
     * @return string
     */
    public function createRequest($token)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                    <RequesterCredentials>
                        <eBayAuthToken>' . $token . '</eBayAuthToken>
                    </RequesterCredentials>
                    <ErrorLanguage>en_US</ErrorLanguage>
                    <WarningLevel>High</WarningLevel>
                    <DetailLevel>ReturnAll</DetailLevel>
                    <ViewAllNodes>true</ViewAllNodes>
                </GetCategoriesRequest>';
    }

    /**
     * @param $request
     * @return mixed
     */
    public function sendRequest($request)
    {
        $headers = $this->ebayHeaders();
        $serverUrl = $this->getUrl();
        $connect = curl_init();
        curl_setopt($connect, CURLOPT_URL, $serverUrl);
        curl_setopt($connect, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($connect, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($connect, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($connect, CURLOPT_POST, 1);
        curl_setopt($connect, CURLOPT_POSTFIELDS, $request);
        curl_setopt($connect, CURLOPT_RETURNTRANSFER, 1);
        $responseXml = curl_exec($connect);
        curl_close($connect);

        return $this->parseResponse($responseXml);
    }

    /**
     * @return string[]
     */
    public function ebayHeaders()
    {
        return [
            'X-EBAY-API-SITEID:0',
            'X-EBAY-API-COMPATIBILITY-LEVEL:967',
            'X-EBAY-API-CALL-NAME:GetCategories'
        ];
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        # Sandbox API url:
        # return 'https://api.sandbox.ebay.com/ws/api.dll';
        return 'https://api.ebay.com/ws/api.dll';
    }

    /**
     * @param $responseXml
     * @return mixed
     */
    public function parseResponse($responseXml)
    {
        $xml = new \SimpleXMLElement($responseXml);
        return json_decode(json_encode($xml));
    }
}
