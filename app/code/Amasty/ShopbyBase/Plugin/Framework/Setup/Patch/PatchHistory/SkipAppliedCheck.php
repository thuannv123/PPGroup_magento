<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Plugin\Framework\Setup\Patch\PatchHistory;

use Amasty\ShopbyBase\Setup\Patch\DeclarativeSchemaApplyBefore\ModifyOptionValueColumn;
use Magento\Framework\Setup\Patch\PatchHistory;

class SkipAppliedCheck
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param PatchHistory $subject
     * @param bool $result
     * @param string $patchName
     * @return bool
     */
    public function afterIsApplied(PatchHistory $subject, $result, $patchName)
    {
        if ($patchName === ModifyOptionValueColumn::class) {
            // need apply this patch each upgrade
            // for supporting downgrade module version
            return false;
        }

        return $result;
    }
}
