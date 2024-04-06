<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace PPGroup\Catalog\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\InputException;
use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token;

/**
 * @inheritdoc
 */
class CaptchaResponseResolver extends \Magento\ReCaptchaUi\Model\CaptchaResponseResolver
{
    protected $_logger;
    
    /**
     * @inheritdoc
     */
    public function resolve(RequestInterface $request): string
    {
        $reCaptchaParam = $request->getParam(self::PARAM_RECAPTCHA);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get(\Magento\Framework\App\RequestInterface::class);
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/recaptcha.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $request =  $request->getParams();
        $logger->info("value form: ".json_encode($request));
        
        if(!isset($request['token'])){
            return $reCaptchaParam;
        }

        if (empty($reCaptchaParam)) {
            /** log recaptcha issue */
            $moduleName = $request->getModuleName();
            $controller = $request->getControllerName();
            $action     = $request->getActionName();
            $route      = $request->getRouteName();
    
            $urlInterface = $objectManager->get(\Magento\Framework\UrlInterface::class);
            $url =$urlInterface->getCurrentUrl();
            $logger->info("$url moduleName:  $moduleName; controller:  $controller; action: $action; route: $route");
            /** end  log recaptcha issue */
            throw new InputException(__('Can not resolve reCAPTCHA parameter.'));
        }
        return $reCaptchaParam;
    }
}
