<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Helper;

class Url extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const ROUTE_SEARCH = 'search';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function validate($url)
    {
        $isUrlValid = true;

        if (strpos((string) $url, '/') !== false) {
            $isUrlValid = false;
            $this->messageManager->addErrorMessage(__('URL route and URL key are not allow /'));
        }

        return $isUrlValid;
    }

    /**
     * @param string $url
     * @return string
     */
    public function prepare($url)
    {
        return str_replace('/', '', $url);
    }

    /**
     * @param string $title
     * @return string|null
     */
    public function generate($title)
    {
        $title = preg_replace('/[«»""!?,.!@£$%^&*{};:()]+/', '', strtolower($title));
        $key = preg_replace('/[^A-Za-z0-9-]+/', '-', $title);

        return $key;
    }

    /**
     * @param $response
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addAmpHeaders($response)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        // @codingStandardsIgnoreLine
        $urlData = parse_url($baseUrl);
        $response
            ->setHeader(
                'Access-Control-Allow-Origin',
                'https://' . str_replace('.', '-', $urlData['host']) . '.cdn.ampproject.org'
            )
            ->setHeader(
                'AMP-Access-Control-Allow-Source-Origin',
                rtrim($this->storeManager->getStore()->getBaseUrl(), '/')
            )
            ->setHeader(
                'Access-Control-Allow-Headers',
                'Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token',
                true
            )
            ->setHeader('Access-Control-Expose-Headers', 'AMP-Access-Control-Allow-Source-Origin', true)
            ->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS', true)
            ->setHeader('Access-Control-Allow-Credentials', 'true', true);
    }
}
