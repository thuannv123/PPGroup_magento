<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Mega Menu Items Tree(System)
 */

namespace Amasty\MegaMenuItemsTree\Ui\Component\Form\Link;

use Amasty\MegaMenuItemsTree\Model\GetCustomLinksCollection;
use Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface;
use Amasty\MegaMenuLite\Model\Backend\DataProvider\LinkRegistry;
use Amasty\MegaMenuLite\Model\Menu\Link;
use Magento\Framework\Data\OptionSourceInterface;

class ItemsTree implements OptionSourceInterface
{
    private const ROOT_ITEM_ID = 0;

    private const VALUE_DATA = 'value';
    private const LABEL_DATA = 'label';
    private const IS_ACTIVE_DATA = 'is_active';
    private const OPTGROUP_DATA = 'optgroup';

    /**
     * @var LinkRegistry
     */
    private $registry;

    /**
     * @var array
     */
    private $linkTree;

    /**
     * @var GetCustomLinksCollection
     */
    private $getCustomLinksCollection;

    public function __construct(
        LinkRegistry $registry,
        GetCustomLinksCollection $getCustomLinksCollection
    ) {
        $this->registry = $registry;
        $this->getCustomLinksCollection = $getCustomLinksCollection;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        return $this->getLinkTree();
    }

    /**
     * Retrieve categories tree
     *
     * @return array
     */
    public function getLinkTree(): array
    {
        if ($this->linkTree === null) {
            $link = $this->registry->getLink();
            $linkById = $this->getRootItemData();
            /** @var LinkInterface $link */
            foreach ($this->getCustomLinksCollection->execute($link) as $link) {
                foreach ([$link->getEntityId(), (int)$link->getParentId()] as $linkId) {
                    if (!isset($linkById[$linkId])) {
                        $linkById[$linkId] = [self::VALUE_DATA => (string) $linkId];
                    }
                }

                $linkById[$link->getId()][self::IS_ACTIVE_DATA] = $link->getStatus();
                $linkById[$link->getId()][self::LABEL_DATA] = $link->getName();
                $linkById[(int)$link->getParentId()][self::OPTGROUP_DATA][] = &$linkById[$link->getId()];
            }
            $this->linkTree = [$linkById[self::ROOT_ITEM_ID]];
        }

        return $this->linkTree;
    }

    private function getRootItemData(): array
    {
        return [
            self::ROOT_ITEM_ID => [
                self::VALUE_DATA => self::ROOT_ITEM_ID,
                self::LABEL_DATA => __('Main Menu'),
                self::IS_ACTIVE_DATA => true
            ],
        ];
    }
}
