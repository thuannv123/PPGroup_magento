<?php declare(strict_types=1);

namespace PPGroup\AccessTrade\Config;

use Magento\Cookie\Helper\Cookie as CookieHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config implements ArgumentInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CookieHelper
     */
    private $cookieHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param CookieHelper $cookieHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CookieHelper $cookieHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->cookieHelper = $cookieHelper;
    }

    /**
     * Check whether the module is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        $enabled = (bool)$this->getModuleConfigValue('enabled', false);
        if (!$enabled) {
            return false;
        }

        return true;
    }

    /**
     * Return the Result ID
     *
     * @return int
     */
    public function getResultId(): int
    {
        return (int)$this->getModuleConfigValue('result_id');
    }

    /**
     * Return the Campaign ID
     *
     * @return string
     */
    public function getCampaignId(): string
    {
        return (string)$this->getModuleConfigValue('campaign_id');
    }

    /**
     * Get tax display mode
     *
     * @return int
     */
    public function getTaxMode(): int
    {
        return (int)$this->getModuleConfigValue('tax_calculation');
    }

    /**
     * Api url
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return (string)$this->getModuleConfigValue('api_url');
    }

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return (bool)$this->getModuleConfigValue('debug');
    }

    /**
     * @return bool
     */
    public function isMergeConfigWithAPI(): bool
    {
        return (bool)$this->getModuleConfigValue('merge_config');
    }

    /**
     * @return int
     */
    public function getIntegrationMethod(): int
    {
        return (int)$this->getModuleConfigValue('integration_method');
    }

    /**
     * @return bool
     */
    public function isClearParametersAfterOrderSuccess(): bool
    {
        return (bool)$this->getModuleConfigValue('clear_parameters_order_success');
    }

    /**
     * @return bool
     */
    public function isUsingUnixTime(): bool
    {
        return (bool)$this->getModuleConfigValue('use_unix_created_time');
    }

    /**
     * @return string
     */
    public function getStoreName(): string
    {
        return (string)$this->getConfigValue('general/store_information/name');
    }

    /**
     * @return string
     */
    public function getCookieRestrictionModeName(): string
    {
        if ($this->cookieHelper->isCookieRestrictionModeEnabled()) {
            return CookieHelper::IS_USER_ALLOWED_SAVE_COOKIE;
        }

        return '';
    }

    /**
     * Return a configuration value
     *
     * @param string $key
     * @param null $defaultValue
     *
     * @return mixed|null
     */
    public function getModuleConfigValue(string $key, $defaultValue = null)
    {
        return $this->getConfigValue('accesstrade/settings/' . $key, $defaultValue);
    }

    /**
     * Return a configuration value
     *
     * @param string $key
     * @param null $defaultValue
     *
     * @return mixed|null
     */
    public function getConfigValue(string $key, $defaultValue = null)
    {
        try {
            $value = $this->scopeConfig->getValue(
                $key,
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()
            );
        } catch (NoSuchEntityException $e) {
            return $defaultValue;
        }

        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }
}
