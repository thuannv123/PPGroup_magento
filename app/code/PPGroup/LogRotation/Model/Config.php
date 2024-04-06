<?php

namespace PPGroup\LogRotation\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const VAR_LOG_ROTATION_ENABLED = 'ppgroup_logroration/general/enabled';
    const VAR_LOG_ROTATION_LIFETIME = 'ppgroup_logroration/general/lifetime';
    const VAR_LOG_ROTATION_ZIP_ENABLE = 'ppgroup_logroration/general/enable_zip';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Smarter Log Enabled
     * @return bool
     */
    public function isLogRotationEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::VAR_LOG_ROTATION_ENABLED,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return int|null
     */
    public function getLogRotationLifeTime(): ?int
    {
        return (int)$this->scopeConfig->getValue(
            self::VAR_LOG_ROTATION_LIFETIME,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return bool
     */
    public function isZipEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::VAR_LOG_ROTATION_ZIP_ENABLE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
