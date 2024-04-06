<?php

declare(strict_types = 1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Mega Menu Items Tree(System)
 */

namespace Amasty\MegaMenuItemsTree\Ui\DataProvider\Form\Link\Modifier;

use Amasty\MegaMenu\Api\Data\Menu\ItemInterface;
use Amasty\MegaMenu\Model\Menu\Subcategory;
use Amasty\MegaMenu\Model\OptionSource\SubcategoriesPosition;
use Amasty\MegaMenu\Api\Data\Menu\LinkInterface;
use Amasty\MegaMenuLite\Model\Backend\DataProvider\LinkRegistry;
use Amasty\MegaMenuLite\Model\Provider\FieldsByStore;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\Store;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class Level implements ModifierInterface
{
    /**
     * @var LinkInterface
     */
    private $entity;

    /**
     * @var SubcategoriesPosition
     */
    private $subcategoriesPosition;

    /**
     * @var Subcategory
     */
    private $subcategory;

    /**
     * @var FieldsByStore
     */
    private $fieldsByStore;

    /**
     * @var int|null
     */
    private $storeId;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    public function __construct(
        SubcategoriesPosition $subcategoriesPosition,
        Subcategory $subcategory,
        FieldsByStore $fieldsByStore,
        LinkRegistry $linkRegistry,
        ArrayManager $arrayManager
    ) {
        $this->subcategoriesPosition = $subcategoriesPosition;
        $this->subcategory = $subcategory;
        $this->fieldsByStore = $fieldsByStore;
        $this->entity = $linkRegistry->getLink();
        $this->storeId = $linkRegistry->getStoreId();
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
        $level = $this->entity->getLevel();
        if ($level === LinkInterface::DEFAULT_LEVEL) {
            $meta = $this->getMainCategoryMeta($meta);
        } else {
            $meta = $this->getSubcategoryMeta($meta);
        }

        return $this->setLevel($meta, $level);
    }

    private function setLevel(array $meta, int $level): array
    {
        foreach ($this->fieldsByStore->getCustomFields() as $fieldsetKey => $fieldSet) {
            foreach ($fieldSet as $field) {
                if ($field === null || $field == ItemInterface::CONTENT) {
                    continue;
                }
                $path = sprintf(
                    '%s/%s/%s/%s/%s',
                    $fieldsetKey,
                    'children',
                    $field,
                    'arguments/data/config',
                    LinkInterface::LEVEL
                );
                $meta = $this->arrayManager->set($path, $meta, $level);
            }
        }

        return $meta;
    }

    private function getMainCategoryMeta(array $meta): array
    {
        $options = $this->subcategoriesPosition->toOptionArray();
        $switcherConfig = true;
        $isShowSubcategories = $this->subcategory->isShowSubcategories(
            $this->entity->getLevel(),
            $this->entity->getEntityId(),
            $this->storeId ?? Store::DEFAULT_STORE_ID,
            LinkInterface::DEFAULT_LEVEL
        );

        if ($isShowSubcategories) {
            $meta = $this->unsetContentNotice($meta);
        }

        return $this->updateMeta($meta, $switcherConfig, $options);
    }

    private function getSubcategoryMeta(array $meta): array
    {
        $options = $this->subcategoriesPosition->toOptionArray(true);
        $switcherConfig = false;

        $parentLinkId = $this->entity->getParentId();
        $isShowSubcategories = $this->subcategory->isShowSubcategories(
            $this->entity->getLevel() - LinkInterface::LEVEL_STEP,
            $parentLinkId,
            $this->storeId ?? Store::DEFAULT_STORE_ID,
            LinkInterface::DEFAULT_LEVEL,
            ItemInterface::CUSTOM_TYPE
        );
        if ($parentLinkId && $isShowSubcategories) {
            $meta = $this->unsetContentNotice($meta);
        }

        return $this->updateMeta($meta, $switcherConfig, $options);
    }

    private function unsetContentNotice(array $meta): array
    {
        unset(
            $meta['am_mega_menu_fieldset']['children']['content']['arguments']['data']['config']['notice']
        );
        unset(
            $meta['am_mega_menu_fieldset']['children']['content']['arguments']['data']['config']['default']
        );

        return $meta;
    }

    private function updateMeta(array $meta, bool $switcherConfig, array $options): array
    {
        $fields = &$meta['am_mega_menu_fieldset']['children'];
        $fields['submenu_type']['arguments']['data']['config']['switcherConfig']['enabled'] = $switcherConfig;
        $fields['subcategories_position']['arguments']['data']['options'] = $options;

        return $meta;
    }
}
