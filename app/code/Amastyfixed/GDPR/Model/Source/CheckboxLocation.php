<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amastyfixed\GDPR\Model\Source;

use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\EntityManager\EventManager;

class CheckboxLocation implements OptionSourceInterface
{
    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(
        EventManager $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => ConsentLogger::FROM_REGISTRATION,
                'label' => __('Registration')
            ],
            [
                'value' => ConsentLogger::FROM_CHECKOUT,
                'label' => __('Checkout')
            ],
            [
                'value' => ConsentLogger::FROM_CONTACTUS,
                'label' => __('Contact Us')
            ],
            [
                'value' => ConsentLogger::FROM_SUBSCRIPTION,
                'label' => __('Newsletter Subscription')
            ],
            [
                'value' => 'popup',
                'label' => __('Newsletter Subscription')
            ],
			[
                'value' => 'admin',
                'label' => __('Admin')
            ],
            [
                'value' => ConsentLogger::FROM_PRIVACY_SETTINGS,
                'label' => __('Optional Consent at Account Privacy Settings')
            ]          
        ];

        return $options;
    }

    /**
     * @return array|false
     */
    public function toArray()
    {
        $optionArray = $this->toOptionArray();
        $labels = array_column($optionArray, 'label');
        $values = array_column($optionArray, 'value');

        return array_combine($values, $labels);
    }
}
