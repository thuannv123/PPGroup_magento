<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Source\RichData;

use Magento\Framework\Data\OptionSourceInterface;

class AuthorType implements OptionSourceInterface
{
    public const NONE = 0;
    public const PERSON = 1;
    public const ORGANIZATION = 2;

    public function toOptionArray()
    {
        return [
            ['value' => self::NONE, 'label' => __('None')],
            ['value' => self::PERSON, 'label' => __('Person')],
            ['value' => self::ORGANIZATION, 'label' => __('Organization')]
        ];
    }
}
