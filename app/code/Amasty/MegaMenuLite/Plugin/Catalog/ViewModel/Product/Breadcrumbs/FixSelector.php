<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Mega Menu Core Base for Magento 2
 */

namespace Amasty\MegaMenuLite\Plugin\Catalog\ViewModel\Product\Breadcrumbs;

use Magento\Catalog\ViewModel\Product\Breadcrumbs as BreadcrumbsViewModel;
use Magento\Framework\Serialize\Serializer\JsonHexTag;

class FixSelector
{
    /**
     * @var JsonHexTag
     */
    private $jsonSerializer;

    public function __construct(JsonHexTag $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetJsonConfigurationHtmlEscaped(
        BreadcrumbsViewModel $subject,
        string $breadcrumbsConfig
    ): string {
        $breadcrumbsConfigArray = $this->jsonSerializer->unserialize($breadcrumbsConfig);
        if (!isset($breadcrumbsConfigArray['breadcrumbs'])) {
            return $breadcrumbsConfig;
        }

        $breadcrumbsConfigArray['breadcrumbs']['menuContainer']
            = '.ammenu-robots-navigation [data-action="navigation"] > ul';

        return $this->jsonSerializer->serialize($breadcrumbsConfigArray);
    }
}
