<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Directory;

use Magento\Framework\App\ActionInterface;

class Currency
{
    /**
     * @var \Amasty\ShopbyBase\Api\UrlBuilderInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    private $encoder;

    public function __construct(
        \Amasty\ShopbyBase\Api\UrlBuilderInterface $urlBuilder,
        \Magento\Framework\Url\EncoderInterface $encoder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->encoder = $encoder;
    }

    /**
     * @param $subject
     * @param \Closure $closure
     * @param $code
     * @return false|string
     */
    public function aroundGetSwitchCurrencyPostData($subject, \Closure $closure, $code)
    {
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = ['_' => null, 'shopbyAjax' => null];
        $currentUrl = $this->urlBuilder->getUrl('*/*/*', $params);

        $url = $subject->escapeUrl($subject->getSwitchUrl());

        $data[ActionInterface::PARAM_NAME_URL_ENCODED] = $this->encoder->encode($currentUrl);

        return json_encode(['action' => $url, 'data' => ['currency' => $code]]);
    }
}
