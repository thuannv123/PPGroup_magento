<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Source\RichData;

use Magento\Framework\Data\OptionSourceInterface;

class Title implements OptionSourceInterface
{
    public const NONE = 0;
    public const TITLE = 1;
    public const META_TITLE = 2;

    public function toOptionArray()
    {
        return [
            ['value' => self::NONE, 'label' => __('None')],
            ['value' => self::TITLE, 'label' => __('Title')],
            ['value' => self::META_TITLE, 'label' => __('Meta Title')]
        ];
    }
}
