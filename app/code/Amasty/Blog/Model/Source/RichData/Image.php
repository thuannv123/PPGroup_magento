<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Source\RichData;

use Magento\Framework\Data\OptionSourceInterface;

class Image implements OptionSourceInterface
{
    public const NONE = 0;
    public const POST_IMAGE = 1;
    public const LIST_IMAGE = 2;

    public function toOptionArray()
    {
        return [
            ['value' => self::NONE, 'label' => __('None')],
            ['value' => self::POST_IMAGE, 'label' => __('Post Image')],
            ['value' => self::LIST_IMAGE, 'label' => __('List Image')]
        ];
    }
}
