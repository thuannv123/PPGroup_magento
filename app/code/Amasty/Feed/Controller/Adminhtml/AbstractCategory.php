<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Controller\Adminhtml;

/**
 * Class Category
 *
 * @package Amasty\Feed
 */
abstract class AbstractCategory extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin action
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_Feed::feed';
}
