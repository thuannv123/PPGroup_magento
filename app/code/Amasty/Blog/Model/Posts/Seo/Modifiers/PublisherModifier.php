<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Posts\Seo\Modifiers;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Helper\Settings as SettingHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;

class PublisherModifier implements ModifierInterface
{
    /**
     * @var SettingHelper
     */
    private $settingsHelper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var LayoutInterface
     */
    private $layout;

    public function __construct(SettingHelper $settingsHelper, UrlInterface $urlBuilder, LayoutInterface $layout)
    {
        $this->settingsHelper = $settingsHelper;
        $this->urlBuilder = $urlBuilder;
        $this->layout = $layout;
    }

    public function modify(PostInterface $post, array $richData): array
    {
        $orgName = $this->settingsHelper->getModuleConfig('search_engine/organization_name');
        if ($orgName) {
            $richData['publisher'] = [
                "@type" => 'Organization',
                'url'   => $this->urlBuilder->getBaseUrl(),
                "name"  => $orgName
            ];

            $logoBlock = $this->layout->getBlock('logo');
            if ($logoBlock) {
                $richData['publisher']['logo'] = $logoBlock->getLogoSrc();
            }
        }

        return $richData;
    }
}
