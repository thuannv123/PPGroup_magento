<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Posts\Seo\Modifiers;

interface ModifierInterface
{
    public function modify(\Amasty\Blog\Api\Data\PostInterface $post, array $richData): array;
}
