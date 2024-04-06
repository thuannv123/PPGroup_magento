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
use DateTimeInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class DateModifier implements ModifierInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(ConfigProvider $configProvider, DateTime $dateTime)
    {
        $this->configProvider = $configProvider;
        $this->dateTime = $dateTime;
    }

    public function modify(PostInterface $post, array $richData): array
    {
        if (!$this->configProvider->isShowPublicationDate()) {
            return $richData;
        }

        $richData['datePublished'] = $this->formatDate($post->getPublishedAt());
        $richData['dateModified'] = $this->formatDate($post->getUpdatedAt());

        return $richData;
    }

    private function formatDate(string $date): string
    {
        return $this->dateTime->date(DateTimeInterface::ATOM, $date);
    }
}
