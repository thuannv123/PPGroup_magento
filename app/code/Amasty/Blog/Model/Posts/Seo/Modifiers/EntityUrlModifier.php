<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Posts\Seo\Modifiers;

use Amasty\Blog\Api\Data\PostInterface;
use Magento\Framework\UrlInterface;

class EntityUrlModifier implements ModifierInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    public function modify(PostInterface $post, array $richData): array
    {
        $richData['mainEntityOfPage'] = $this->urlBuilder->getUrl(
            '*/*/*',
            ['_current' => true, '_use_rewrite' => true]
        );

        return $richData;
    }
}
