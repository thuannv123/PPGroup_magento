<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Infinite Scroll for Magento 2
 */

namespace Amasty\Scroll\Helper;

use Amasty\Scroll\Model\Source\Loading;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public const MODULE_PATH = 'amasty_scroll/';

    /**
     * @param $path
     * @param int $storeId
     *
     * @return mixed
     */
    public function getModuleConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::MODULE_PATH . $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getModuleConfig('general/loading') !== Loading::NONE;
    }
}
