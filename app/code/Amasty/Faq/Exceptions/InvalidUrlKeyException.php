<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package FAQ and Product Questions for Magento 2
 */

namespace Amasty\Faq\Exceptions;

class InvalidUrlKeyException extends \Magento\Framework\Exception\LocalizedException
{
    public function __construct(\Magento\Framework\Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if (!$phrase) {
            $phrase = __('Invalid Url key. Only a-z, 0-9, ., -, _ expected.');
        }
        parent::__construct($phrase, $cause, (int) $code);
    }
}
