<?php

declare(strict_types = 1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Mega Menu Items Tree(System)
 */

namespace Amasty\MegaMenuItemsTree\Ui\DataProvider\Form\Link\Modifier;

use Amasty\MegaMenuPremium\Api\Data\Menu\ItemInterface;
use Amasty\MegaMenu\Api\Data\Menu\LinkInterface;
use Amasty\MegaMenu\Model\Provider\FieldsToHideProvider;
use Amasty\MegaMenuItemsTree\Model\ResourceModel\Link\IsHasChild;
use Amasty\MegaMenuLite\Model\Backend\DataProvider\LinkRegistry;
use Magento\Catalog\Model\Category;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\Store;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class HideItems implements ModifierInterface
{
    /**
     * @var Category
     */
    private $entity;

    /**
     * @var int|null
     */
    private $storeId;

    /**
     * @var FieldsToHideProvider
     */
    private $fieldsToHideProvider;

    /**
     * @var IsHasChild
     */
    private $isHasChild;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    public function __construct(
        FieldsToHideProvider $fieldsToHideProvider,
        LinkRegistry $linkRegistry,
        IsHasChild $isHasChild,
        ArrayManager $arrayManager
    ) {
        $this->fieldsToHideProvider = $fieldsToHideProvider;
        $this->entity = $linkRegistry->getLink();
        $this->storeId = $linkRegistry->getStoreId();
        $this->isHasChild = $isHasChild;
        $this->arrayManager = $arrayManager;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $this->modifyLevel($meta);
    }

    private function modifyLevel(array $meta): array
    {
        if ($this->entity->getLevel() !== LinkInterface::DEFAULT_LEVEL) {
            $itemsToHide = $this->getSubcategoryFields();
            $this->updateItemsToHide($itemsToHide);
        }

        return $this->hideFields($meta, array_unique($itemsToHide ?? []));
    }

    private function getSubcategoryFields(): array
    {
        return $this->fieldsToHideProvider->getSubcategoryFields(
            $this->entity->getLevel() - LinkInterface::LEVEL_STEP,
            (int) $this->entity->getParentId(),
            $this->storeId ?? Store::DEFAULT_STORE_ID,
            LinkInterface::DEFAULT_LEVEL,
            ItemInterface::CUSTOM_TYPE
        );
    }

    private function updateItemsToHide(array &$itemsToHide): void
    {
        if (!$this->isHasChild->execute($this->entity)) {
            $itemsToHide[] = ItemInterface::SUBCATEGORIES_POSITION;
            $itemsToHide[] = ItemInterface::SUBMENU_TYPE;
        }

        if ($this->entity->isObjectNew()) {
            $itemsToHide[] = ItemInterface::SUBMENU_TYPE;
        }
        $itemsToHide[] = ItemInterface::SUBMENU_ANIMATION;
    }

    private function hideFields(array $meta, array $fieldsToHide): array
    {
        $megaMenuFieldsetItems = [
            ItemInterface::CONTENT,
            ItemInterface::HIDE_CONTENT,
            ItemInterface::SUBMENU_TYPE,
            ItemInterface::COLUMN_COUNT,
            ItemInterface::SUBCATEGORIES_POSITION,
            ItemInterface::WIDTH,
            ItemInterface::WIDTH_VALUE,
            ItemInterface::SUBMENU_ANIMATION
        ];
        foreach ($fieldsToHide as $field) {
            $fieldSet = in_array($field, $megaMenuFieldsetItems)
                ? 'am_mega_menu_fieldset'
                : 'general';
            $basePath = $this->getBasePath($fieldSet, $field);

            $path = sprintf('%s/%s', $basePath, 'hidden');
            $meta = $this->arrayManager->set($path, $meta, true);

            $path = sprintf('%s/%s', $basePath, 'visible');
            $meta = $this->arrayManager->set($path, $meta, false);
        }

        return $meta;
    }

    private function getBasePath(string $fieldSet, string $field): string
    {
        return sprintf('%s/%s/%s/%s/%s/%s', $fieldSet, 'children', $field, 'arguments', 'data', 'config');
    }
}
