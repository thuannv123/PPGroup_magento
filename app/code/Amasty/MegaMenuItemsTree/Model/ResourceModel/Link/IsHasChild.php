<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Mega Menu Items Tree(System)
 */

namespace Amasty\MegaMenuItemsTree\Model\ResourceModel\Link;

use Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class IsHasChild extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(LinkInterface::TABLE_NAME, LinkInterface::ENTITY_ID);
    }

    public function execute(LinkInterface $link): bool
    {
        $select = $this->getConnection()->select()
            ->from(['main_table' => $this->getMainTable()])
            ->where(
                sprintf('main_table.%s LIKE ?', LinkInterface::PATH),
                sprintf('%s%s%s%%', $link->getPath(), $link->getEntityId(), LinkInterface::PATH_SEPARATOR)
            )
            ->limit(1);

        return (bool) $this->getConnection()->fetchOne($select);
    }
}
