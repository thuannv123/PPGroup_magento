<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation GraphQl for Magento 2 (System)
 */

namespace Amasty\ShopbyGraphQl\Model\Resolver;

use Amasty\ShopbySeo\Helper\UrlParser;
use Amasty\ShopbySeo\Model\UrlParser\Url\ParseSeoUrl;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class RetrieveSeoParams implements ResolverInterface
{
    /**
     * @var ParseSeoUrl
     */
    private $parseSeoUrl;

    /**
     * @var UrlParser
     */
    private $urlParser;

    public function __construct(ParseSeoUrl $parseSeoUrl, UrlParser $urlParser)
    {
        $this->parseSeoUrl = $parseSeoUrl;
        $this->urlParser = $urlParser;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $urlParts = parse_url($args['url']);
        $url = $urlParts['path'] ?? $args['url'];
        if (substr($url, 0, 1) === '/' && $url !== '/') {
            $url = ltrim($url, '/');
        }

        if ($result = $this->parseSeoUrl->execute($url)) {
            [$seoPart] = $result;
            $params = $this->urlParser->parseSeoPart($seoPart);
            return $this->prepareResult($params);
        }

        return [];
    }

    private function prepareResult(array $params): array
    {
        $result = [];
        foreach ($params as $paramName => $paramValue) {
            $result[] = [
                'code' => $paramName,
                'value' => explode(',', (string)$paramValue)
            ];
        }

        return $result;
    }
}
