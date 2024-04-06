<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\CurlProxy\Controller\Adminhtml\Product\Gallery;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RetrieveImage extends \Magento\ProductVideo\Controller\Adminhtml\Product\Gallery\RetrieveImage
{
    protected $_scopeConfig;
    public function __construct( \Magento\Backend\App\Action\Context $context,
                                 \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
                                 \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
                                 \Magento\Framework\Filesystem $fileSystem,
                                 \Magento\Framework\Image\AdapterFactory $imageAdapterFactory,
                                 \Magento\Framework\HTTP\Adapter\Curl $curl,
                                 \Magento\MediaStorage\Model\ResourceModel\File\Storage\File $fileUtility,
                                 \Magento\Framework\Validator\ValidatorInterface $protocolValidator = null,
                                 \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $extensionValidator = null,
                                 \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context,
            $resultRawFactory,
            $mediaConfig,
            $fileSystem,
            $imageAdapterFactory,
            $curl,
            $fileUtility,
            $protocolValidator,
            $extensionValidator
        );
    }
    /**
     * @param string $fileUrl
     * @param string $localFilePath
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function retrieveRemoteImage($fileUrl, $localFilePath)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $fileUrl);
        if($this->_scopeConfig->getValue('curl_proxy/curl_proxy_setup/proxy_enabled')) {
            curl_setopt($curl, CURLOPT_PROXY,$this->_scopeConfig->getValue('curl_proxy/curl_proxy_setup/proxy_url'));
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array());
        curl_setopt($curl, CURLOPT_HEADER, false);
        $response = curl_exec($curl);
        $response = preg_replace('/Transfer-Encoding:\s+chunked\r?\n/i', '', $response);
        $image = $response;
        if (empty($image)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Could not get preview image information. Please check your connection and try again.')
            );
        }
        $this->fileUtility->saveFile($localFilePath, $image);
    }
}
