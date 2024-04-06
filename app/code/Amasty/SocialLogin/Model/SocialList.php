<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Social Login Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SocialLogin\Model;

use Magento\Framework\Data\OptionSourceInterface;

class SocialList implements OptionSourceInterface
{
    /**
     * Undefined social type.
     */
    public const TYPE_GENERAL = 'general';

    /**
     * Supported Social Media Types.
     */
    public const TYPE_GOOGLE = 'google';

    public const TYPE_FACEBOOK = 'facebook';

    public const TYPE_TWITTER = 'twitter';

    public const TYPE_LINKEDIN = 'linkedin';

    public const TYPE_INSTAGRAM = 'instagram';

    public const TYPE_AMAZON = 'amazon';

    public const TYPE_PAYPAL = 'paypal';

    public const TYPE_TWITCH = 'twitch';

    /**
     * Apple type.
     * Separated extension social.
     */
    public const TYPE_APPLE = 'apple';

    /**
     * Social Media names.
     */
    public const NAME_GOOGLE = 'Google';

    public const NAME_FACEBOOK = 'Facebook';

    public const NAME_TWITTER = 'Twitter';

    public const NAME_LINKEDIN = 'LinkedIn';

    public const NAME_INSTAGRAM = 'Instagram';

    public const NAME_AMAZON = 'Amazon';

    public const NAME_PAYPAL = 'Paypal';

    public const NAME_TWITCH = 'TwitchTV';

    /**
     * Array of mapped social types with names.
     *
     * @return string[]
     */
    public function getList(): array
    {
        return [
            self::TYPE_GOOGLE => self::NAME_GOOGLE,
            self::TYPE_FACEBOOK => self::NAME_FACEBOOK,
            self::TYPE_TWITTER => self::NAME_TWITTER,
            self::TYPE_LINKEDIN => self::NAME_LINKEDIN,
            self::TYPE_INSTAGRAM => self::NAME_INSTAGRAM,
            self::TYPE_AMAZON => self::NAME_AMAZON,
            self::TYPE_PAYPAL => self::NAME_PAYPAL,
            self::TYPE_TWITCH => self::NAME_TWITCH,
        ];
    }

    /**
     * @param string $type
     * @return string
     */
    public function getNameByType(string $type): string
    {
        return $this->getList()[$type] ?? self::TYPE_GENERAL;
    }

    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->getList() as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }

        return $options;
    }
}
