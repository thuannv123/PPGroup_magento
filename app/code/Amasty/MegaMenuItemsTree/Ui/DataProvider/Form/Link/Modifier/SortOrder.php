<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Mega Menu Items Tree(System)
 */

namespace Amasty\MegaMenuItemsTree\Ui\DataProvider\Form\Link\Modifier;

use Amasty\MegaMenuItemsTree\Model\ResourceModel\Link\GetLinkSortOrder;
use Amasty\MegaMenuLite\Api\Data\Menu\ItemInterface;
use Amasty\MegaMenuLite\Model\Backend\DataProvider\DataCollectorInterface;

class SortOrder implements DataCollectorInterface
{
    /**
     * @var GetLinkSortOrder
     */
    private $getLinkSortOrder;

    public function __construct(
        GetLinkSortOrder $getLinkSortOrder
    ) {
        $this->getLinkSortOrder = $getLinkSortOrder;
    }

    public function execute(array $data, int $storeId, int $entityId): array
    {
        $data[ItemInterface::SORT_ORDER] = $this->getLinkSortOrder->execute(
            $entityId,
            $storeId
        );

        return $data;
    }
}
