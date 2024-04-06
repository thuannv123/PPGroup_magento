<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Mega Menu Items Tree(System)
 */

namespace Amasty\MegaMenuItemsTree\Model\ResourceModel\Link;

use Amasty\MegaMenu\Api\Data\Menu\ItemInterface;
use Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface;
use Amasty\MegaMenuLite\Model\ResourceModel\Menu\Item\Position;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Store;

class GetLinkSortOrder extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(LinkInterface::TABLE_NAME, LinkInterface::ENTITY_ID);
    }

    public function execute(int $entityId, ?int $store, string $type = ItemInterface::CUSTOM_TYPE): int
    {
        $sortOrderCol = $this->getConnection()->getIfNullSql(
            sprintf('store_item_order.%s', ItemInterface::SORT_ORDER),
            sprintf('default_item_order.%s', ItemInterface::SORT_ORDER)
        );
        $select = $this->getConnection()->select()
            ->from(['main_table' => $this->getMainTable()], [ItemInterface::SORT_ORDER => $sortOrderCol])
            ->where(sprintf('main_table.%s = ?', LinkInterface::ENTITY_ID), $entityId);

        $select = $this->joinPositionTable($select, $type, Store::DEFAULT_STORE_ID, 'default_item_order');
        $select = $this->joinPositionTable($select, $type, $store, 'store_item_order');

        return (int) $this->getConnection()->fetchOne($select);
    }

    private function joinPositionTable(Select $select, string $type, int $store, string $altname): Select
    {
        $defaultCond = sprintf(
            '%1$s.%2$s = main_table.%2$s AND %1$s.%3$s = "%4$s" AND %1$s.%5$s = %6$d',
            $altname,
            Position::ENTITY_ID,
            Position::TYPE,
            $type,
            Position::STORE_VIEW,
            $store
        );

        $select->joinLeft(
            [$altname => $this->getTable(Position::TABLE)],
            $defaultCond,
            []
        );

        return $select;
    }
}
