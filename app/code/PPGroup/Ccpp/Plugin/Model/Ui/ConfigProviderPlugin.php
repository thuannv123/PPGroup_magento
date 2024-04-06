<?php

namespace PPGroup\Ccpp\Plugin\Model\Ui;

use Acommerce\Ccpp\Model\Ui\ConfigProvider;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\StoreManagerInterface;
use PPGroup\Ccpp\Model\Config\Backend\Image;

class ConfigProviderPlugin
{
    protected $paymentHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FilterProvider
     */
    private $contentProcessor;

    public function __construct(
        PaymentHelper $paymentHelper,
        StoreManagerInterface $storeManager,
        FilterProvider $contentProcessor
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->storeManager = $storeManager;
        $this->contentProcessor = $contentProcessor;
    }

    public function afterGetConfig(ConfigProvider $subject, $result)
    {
        $data = [];
        if (!is_array($result) || empty($result)) {
            return $result;
        }
        if (isset($result['payment']['ccpp'])) {
            $methodInstance = $this->paymentHelper->getMethodInstance('ccpp');
            $data['logo'] = $this->getCustomLogoUrl($methodInstance->getConfigData('logo'));
            $data['additional_info'] = $this->getAdditionalData($methodInstance->getConfigData('additional_info'));
            $data = array_merge($data, $result['payment']['ccpp']);

            $result['payment']['ccpp'] = $data;
        }
        return $result;
    }

    /**
     * @param $config
     * @return string
     */
    protected function getCustomLogoUrl($config): string
    {
        $fullPath = '';

        if (empty($config)) {
            return $fullPath;
        }
        try {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $urlPath = Image::UPLOAD_DIR . '/' . $config;
            $fullPath = sprintf('%s%s', $mediaUrl, $urlPath);
        } catch (\Exception $e) {
            $fullPath = '';
        }

        return $fullPath;
    }

    /**
     * @param $config
     * @return string
     * @throws \Exception
     */
    protected function getAdditionalData($config): string
    {
        if (empty($config)) {
            return '';
        }
        return $this->contentProcessor->getPageFilter()->filter($config);
    }
}
