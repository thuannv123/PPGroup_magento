<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Social Login Base for Magento 2
*/

namespace Amasty\SocialLogin\Plugin;


use Amasty\InvisibleCaptcha\Model\Captcha;

class InvisibleCaptcha
{
    /**
     * @param Captcha $subject
     * @param string $result
     *
     * @return string
     */
    public function afterGetSelectors(Captcha $subject, $result)
    {
        $result .= ', .am-login-popup .form';

        return $result;
    }
}
