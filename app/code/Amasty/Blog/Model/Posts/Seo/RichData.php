<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Posts\Seo;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\Posts\Seo\Modifiers\ModifierInterface;
use LogicException;

class RichData
{
    /**
     * @var ModifierInterface[]
     */
    private $modifiersPool;

    public function __construct(array $modifiersPool = [])
    {
        $this->initModifiers($modifiersPool);
    }

    public function get(PostInterface $post): array
    {
        $richData = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting'
        ];

        foreach ($this->modifiersPool as $modifier) {
            $richData = $modifier->modify($post, $richData);
        }

        return $richData;
    }

    /**
     * @param ModifierInterface[] $modifiersPool
     * @return void
     * @throws LogicException
     */
    private function initModifiers(array $modifiersPool): void
    {
        foreach ($modifiersPool as $modifier) {
            if (!$modifier instanceof ModifierInterface) {
                throw new LogicException(
                    sprintf('Modifier must implement %s', ModifierInterface::class)
                );
            }
        }

        $this->modifiersPool = $modifiersPool;
    }
}
