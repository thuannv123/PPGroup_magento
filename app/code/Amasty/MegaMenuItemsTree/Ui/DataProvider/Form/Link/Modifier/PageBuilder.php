<?php

declare(strict_types = 1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Mega Menu Items Tree(System)
 */

namespace Amasty\MegaMenuItemsTree\Ui\DataProvider\Form\Link\Modifier;

use Amasty\MegaMenuLite\Model\Menu\Content\Resolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\Manager;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class PageBuilder implements ModifierInterface
{
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        Manager $moduleManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->moduleManager = $moduleManager;
        $this->scopeConfig = $scopeConfig;
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
        return $this->modifyPageBuilder($meta);
    }

    private function modifyPageBuilder(array $meta): array
    {
        $config = &$meta['am_mega_menu_fieldset']['children']['content']['arguments']['data']['config'];
        if ($this->isPageBuilderEnabled()) {
            $config['default'] = Resolver::CHILD_ITEMS;
            $config['component'] = 'Amasty_MegaMenuLite/js/form/components/wysiwyg';
        } else {
            $config['default'] = Resolver::CHILD_ITEMS;
            $config['component'] = 'Amasty_MegaMenuLite/js/form/element/wysiwyg';
            $config['notice'] = __(
                'You can use the variable: {{child_items_content}} for showing child items.'
            );
            $config['defaultNotice'] = $config['notice'];
        }

        return $meta;
    }

    private function isPageBuilderEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Magento_PageBuilder')
            && $this->scopeConfig->getValue('cms/pagebuilder/enabled', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }
}
