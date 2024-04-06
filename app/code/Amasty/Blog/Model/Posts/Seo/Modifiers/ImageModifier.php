<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Posts\Seo\Modifiers;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Helper\Image as ImageHelper;
use Amasty\Blog\Model\ConfigProvider;
use Amasty\Blog\Model\Source\RichData\Image;

class ImageModifier implements ModifierInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    public function __construct(ConfigProvider $configProvider, ImageHelper $imageHelper)
    {
        $this->imageHelper = $imageHelper;
        $this->configProvider = $configProvider;
    }

    public function modify(PostInterface $post, array $richData): array
    {
        $imageUrl = $this->resolveImage($post);
        if ($imageUrl !== null) {
            $richData['image'] = $imageUrl;
        }

        return $richData;
    }

    private function resolveImage(PostInterface $post): ?string
    {
        $showImage = $this->configProvider->getShowImage();
        if ($showImage === Image::NONE) {
            return null;
        }

        $images = [$post->getPostThumbnail(), $post->getListThumbnail()];
        if ($showImage === Image::LIST_IMAGE) {
            krsort($images);
        }

        foreach ($images as $image) {
            if ($image) {
                return $this->imageHelper->getImageUrl($image);
            }
        }

        return null;
    }
}
