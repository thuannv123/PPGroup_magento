<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Grouped Options for Magento 2
 */

namespace Amasty\GroupedOptions\Api;

interface GroupRepositoryInterface
{
    public const TABLE = 'amasty_grouped_options_group';
    public const TABLE_OPTIONS = 'amasty_grouped_options_group_option';
    public const TABLE_VALUES = 'amasty_grouped_options_group_value';

    /**
     * @param $groupCode
     * @return false or array
     */
    public function getGroupOptionsIds($groupCode);
}
