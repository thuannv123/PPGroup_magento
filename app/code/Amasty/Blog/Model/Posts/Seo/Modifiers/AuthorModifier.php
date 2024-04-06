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
use Amasty\Blog\Model\Source\RichData\AuthorType;

class AuthorModifier implements ModifierInterface
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
        if (!$post->getAuthorId() || $this->configProvider->getShowAuthorType() === AuthorType::NONE) {
            return $richData;
        }

        $authorData = [];
        if ($type = $this->getType()) {
            $authorData['@type'] = $type;
        }
        if ($this->configProvider->isShowAuthorName()) {
            $authorData['name'] = $post->getPostedBy();
        }
        if ($this->configProvider->isShowAuthorUrl()) {
            $authorData['url'] = $post->getAuthor()->getUrl();
        }
        $richData['author'] = $authorData;

        return $richData;
    }

    private function getType(): ?string
    {
        switch ($this->configProvider->getShowAuthorType()) {
            case AuthorType::PERSON:
                return 'Person';
            case AuthorType::ORGANIZATION:
                return 'Organization';
            case AuthorType::NONE:
            default:
                return null;
        }
    }
}
