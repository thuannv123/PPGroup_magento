<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Posts\Seo\Modifiers;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\ConfigProvider;
use Amasty\Blog\Model\Source\RichData\Title;

class HeadlineModifier implements ModifierInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function modify(PostInterface $post, array $richData): array
    {
        $title = $this->resolveTitle($post);
        if ($title !== null) {
            $richData['headline'] = $title;
            $richData['name'] = $title;
        }
        $richData['description'] = $post->getShortContent();

        return $richData;
    }

    private function resolveTitle(PostInterface $post): ?string
    {
        $showTitle = $this->configProvider->getShowTitle();
        if ($showTitle === Title::NONE) {
            return null;
        }

        $titles = [$post->getTitle(), $post->getMetaTitle()];
        if ($this->configProvider->getShowTitle() === Title::META_TITLE) {
            krsort($titles);
        }

        foreach ($titles as $title) {
            if ($title) {
                return $title;
            }
        }

        return '';
    }
}
