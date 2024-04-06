<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Layout\Modal;

use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\Layout\LayoutProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;

class CookieSettings implements LayoutProcessorInterface
{
    public const COMPONENT_NAME = 'gdpr-cookie-settings-modal';
    public const COMPONENT_JS = 'Amasty_GdprFrontendUi/js/modal/cookie-settings';
    private const LOCATION_PATH_IN_LAYOUT = 'jsComponents/components/gdpr-cookie-modal';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    public function __construct(
        ConfigProvider $configProvider,
        ArrayManager $arrayManager
    ) {
        $this->configProvider = $configProvider;
        $this->arrayManager = $arrayManager;
    }

    public function process(array $jsLayout): array
    {
        $mainModal = $this->arrayManager->get(self::LOCATION_PATH_IN_LAYOUT, $jsLayout);
        if (!$mainModal) {
            return $jsLayout;
        }

        $component = [
            self::COMPONENT_NAME => [
                'component' => self::COMPONENT_JS,
            ]
        ];
        if ($settings = $this->getSettings()) {
            $component[self::COMPONENT_NAME]['settings'] = $settings;
        }
        $mainModal['children'] = $component;

        return $this->arrayManager->set(self::LOCATION_PATH_IN_LAYOUT, $jsLayout, $mainModal);
    }

    private function getSettings(): array
    {
        return array_filter([
            'backgroundColor' => $this->configProvider->getCookieSettingsBarBackgroundColor(),
            'groupTitleTextColor' => $this->configProvider->getCookieSettingsBarGroupTitleTextColor(),
            'groupDescriptionTextColor' => $this->configProvider->getCookieSettingsBarGroupDescriptionTextColor(),
            'groupLinksColor' => $this->configProvider->getCookieSettingsBarGroupLinksColor(),
            'doneButtonText' => $this->configProvider->getCookieSettingsBarDoneButtonText(),
            'doneButtonColor' => $this->configProvider->getCookieSettingsBarDoneButtonColor(),
            'doneButtonColorHover' => $this->configProvider->getCookieSettingsBarDoneButtonColorHover(),
            'doneButtonTextColor' => $this->configProvider->getCookieSettingsBarDoneButtonTextColor(),
            'doneButtonTextColorHover' => $this->configProvider->getCookieSettingsBarDoneButtonTextColorHover(),
        ]);
    }
}
