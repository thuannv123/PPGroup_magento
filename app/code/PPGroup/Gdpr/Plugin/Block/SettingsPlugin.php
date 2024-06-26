<?php

namespace PPGroup\Gdpr\Plugin\Block;

use Amasty\Gdpr\Block\Settings;

class SettingsPlugin
{
    public function afterGetPrivacySettings(
        Settings $subject,
        $result
    ) {
        if (is_array($result) && isset($result['delete'])) {
            $result['delete']['content'] = __('Request to remove your account, together with all your personal data,will be processed by our staff.<br>Deleting your account will remove all the purchase history, discounts, orders, invoices and all other information that might be related to your account or your purchases.<br>All your orders and similar information will be lost.<br>You will not be able to restore access to your account after we approve your removal request.');
        }
        return $result;
    }
}
