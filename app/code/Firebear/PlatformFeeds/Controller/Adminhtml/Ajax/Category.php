<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Controller\Adminhtml\Ajax;

use Magento\Framework\Exception\NotFoundException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Firebear\PlatformFeeds\Model\Mapping\FeedList;
use Firebear\PlatformFeeds\Helper\Data;
use Firebear\PlatformFeeds\Helper\Api\Ebay;
use Firebear\PlatformFeeds\Helper\Api\Amazon;
use Firebear\PlatformFeeds\Helper\Api\Google;
use Firebear\PlatformFeeds\Helper\Api\Yandex;

class Category extends Action
{
    /**
     * @var FeedList
     */
    protected $feedList;

    /**
     * @var Data
     */
    public $helper;

    /**
     * @var Ebay
     */
    protected $ebay;

    /**
     * @var Amazon
     */
    protected $amazon;

    /**
     * @var Google
     */
    protected $google;

    /**
     * @var Yandex
     */
    protected $yandex;

    /**
     * Category constructor.
     * @param Context $context
     * @param Data $helper
     * @param FeedList $feedList
     * @param Ebay $ebay
     * @param Amazon $amazon
     * @param Google $google
     * @param Yandex $yandex
     */
    public function __construct(
        Context $context,
        Data $helper,
        FeedList $feedList,
        Ebay $ebay,
        Amazon $amazon,
        Google $google,
        Yandex $yandex
    ) {
        parent::__construct($context);

        $this->helper = $helper;
        $this->feedList = $feedList;
        $this->ebay = $ebay;
        $this->amazon = $amazon;
        $this->google = $google;
        $this->yandex = $yandex;
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create($this->resultFactory::TYPE_JSON);
        if ($this->getRequest()->isAjax()) {
            $formData  = $this->getRequest()->getPost();
            return $resultJson->setData(
                [
                    'category' => $this->getCategoryByEntityFeed($formData)
                ]
            );
        }

        throw new NotFoundException(__('Ajax request only'));
    }

    /**
     * @param $formData
     * @return array|bool|float|int|mixed|string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryByEntityFeed($formData)
    {
        $typeId = $formData['type_id'];
        if (empty($formData['id'])) {
            $id = $this->helper->getNextEntityId();
        } else {
            $id = $formData['id'];
        }

        $categories = [];
        $feedList = $this->feedList->feeds;
        switch ($feedList[$typeId]) {
            case 'Ebay':
                $categories = $this->ebay->getCategory($formData, $id, $typeId);
                break;
            case 'Amazon':
                $categories = $this->amazon->getCategory($formData, $id, $typeId);
                break;
            case 'Yandex':
                $categories = $this->yandex->getCategory($formData, $id, $typeId);
                break;
            case ('Google' || 'Facebook'):
                $categories = $this->google->getCategory($formData, $id, $typeId);
                break;
        }

        return $categories;
    }
}
