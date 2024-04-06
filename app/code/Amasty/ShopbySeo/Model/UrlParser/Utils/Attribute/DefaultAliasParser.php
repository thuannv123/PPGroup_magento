<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\UrlParser\Utils\Attribute;

use Amasty\ShopbySeo\Model\SeoOptions;
use Amasty\ShopbySeo\Model\UrlParser\Utils\ParamsUpdater;

class DefaultAliasParser implements ParserInterface
{
    /**
     * @var SeoOptions
     */
    private $seoOptions;

    /**
     * @var ParamsUpdater
     */
    private $paramsUpdater;

    /**
     * @var ParsingResultValidator
     */
    private $resultValidator;

    public function __construct(
        SeoOptions $seoOptions,
        ParamsUpdater $paramsUpdater,
        ParsingResultValidator $resultValidator
    ) {
        $this->seoOptions = $seoOptions;
        $this->paramsUpdater = $paramsUpdater;
        $this->resultValidator = $resultValidator;
    }

    /**
     * Parse prepared aliases and update request
     *
     * @param array $aliases
     * @param string $seoPart
     * @return array
     */
    public function parse(array $aliases, string $seoPart): array
    {
        $attributeOptionsData = $this->seoOptions->getData();

        $parsedAttributeCodes = [];
        $parsedAliases = [];
        $params = [];
        $currentAttributeCode = '';
        foreach ($aliases as $currentAlias) {
            if (in_array($currentAlias, array_keys($attributeOptionsData))
                && !in_array($currentAlias, $parsedAttributeCodes) // the attribute code can be equal to its value
            ) {
                $currentAttributeCode = $currentAlias;
                $parsedAttributeCodes[] = $currentAttributeCode;
                continue;
            }

            if ($currentAttributeCode) {
                $optionsData = $attributeOptionsData[$currentAttributeCode];
                foreach ($optionsData as $optionId => $optionAlias) {
                    if ($currentAlias === $optionAlias) {
                        $parsedAliases[] = $currentAlias;
                        $this->paramsUpdater->update($params, $currentAttributeCode, (string) $optionId);
                    }
                }
            }
        }

        return $this->resultValidator->validate($seoPart, $parsedAttributeCodes, $parsedAliases) ? $params : [];
    }
}
