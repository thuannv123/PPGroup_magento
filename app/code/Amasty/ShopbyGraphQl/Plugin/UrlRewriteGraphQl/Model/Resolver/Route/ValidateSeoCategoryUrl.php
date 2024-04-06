<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation GraphQl for Magento 2 (System)
 */

namespace Amasty\ShopbyGraphQl\Plugin\UrlRewriteGraphQl\Model\Resolver\Route;

use Amasty\ShopbySeo\Helper\UrlParser;
use Amasty\ShopbySeo\Model\UrlParser\Url\ParseSeoUrl;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\UrlRewriteGraphQl\Model\Resolver\Route as RouteResolver;

class ValidateSeoCategoryUrl
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
     * @param RouteResolver $subject
     * @param callable $proceed
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundResolve(
        RouteResolver $subject,
        callable $proceed,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['url'])) {
            return $proceed($field, $context, $info, $value, $args);
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $urlParts = parse_url($args['url']);
        $url = $urlParts['path'] ?? $args['url'];
        if (substr($url, 0, 1) === '/' && $url !== '/') {
            $url = ltrim($url, '/');
        }

        if ($result = $this->parseSeoUrl->execute($url)) {
            [$seoPart, $identifier] = $result;
            if ($this->urlParser->parseSeoPart($seoPart)) {
                $args['url'] = $identifier;
                $result = $proceed($field, $context, $info, $value, $args);
                if ($result) {
                    $result['relative_url'] = $url;
                }
                return $result;
            }
        }

        return $proceed($field, $context, $info, $value, $args);
    }
}
