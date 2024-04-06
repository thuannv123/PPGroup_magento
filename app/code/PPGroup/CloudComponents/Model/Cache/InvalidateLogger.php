<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PPGroup\CloudComponents\Model\Cache;

/**
 * Log cache invalidation to a file
 */
class InvalidateLogger extends \Magento\CloudComponents\Model\Cache\InvalidateLogger
{
    /**
     * Log cache invalidation to a file
     *
     * @param mixed $invalidateInfo
     */
    public function execute($invalidateInfo)
    {
        $invalidateInfo = array();
        $invalidateInfo['trace'] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        parent::execute($invalidateInfo);
    }
}
