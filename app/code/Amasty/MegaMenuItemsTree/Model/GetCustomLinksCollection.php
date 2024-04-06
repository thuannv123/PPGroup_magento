<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Mega Menu Items Tree(System)
 */

namespace Amasty\MegaMenuItemsTree\Model;

use Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface;
use Amasty\MegaMenuLite\Model\ResourceModel\Menu\Link\Collection as LinkCollection;
use Amasty\MegaMenuLite\Model\ResourceModel\Menu\Link\CollectionFactory as LinkCollectionFactory;
use Magento\Store\Model\Store;

class GetCustomLinksCollection
{
    /**
     * @var LinkCollectionFactory
     */
    private $linkCollectionFactory;

    public function __construct(
        LinkCollectionFactory $linkCollectionFactory
    ) {
        $this->linkCollectionFactory = $linkCollectionFactory;
    }

    /**
     * Get Custom Links Collection and exclude $link and its children
     *
     * @param LinkInterface|null $link
     * @return LinkCollection
     */
    public function execute(?LinkInterface $link, ?int $storeId = Store::DEFAULT_STORE_ID): LinkCollection
    {
        $collection = $this->linkCollectionFactory->create();
        $collection->joinItemOrder($storeId, []);
        $collection->addSortOrder($storeId);

        if ($link && $link->getEntityId()) {
            $excludePath = $link->getPath() . '/' . $link->getEntityId();
            $collection->excludePath($excludePath);
            $collection->excludeByEntityId($link->getEntityId());
        }

        return $collection;
    }
}
