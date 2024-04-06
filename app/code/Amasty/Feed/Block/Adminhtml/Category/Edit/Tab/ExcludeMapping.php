<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Block\Adminhtml\Category\Edit\Tab;

use Magento\Catalog\Block\Adminhtml\Category\AbstractCategory;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class ExcludeMapping extends AbstractCategory implements RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'category/exclude_mapping.phtml';

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function getCategoriesList()
    {
        $list = [];
        $root = $this->getRoot(null, 10);
        if ($root->hasChildren()) {
            foreach ($root->getChildren() as $node) {
                $this->getChildCategories($list, $node);
            }
        }

        return $list;
    }

    private function getChildCategories(&$list, $node, $level = 0)
    {
        $list[] = [
            'name'  => $node->getName(),
            'id'    => $node->getId(),
            'level' => $level
        ];

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->getChildCategories($list, $child, $level + 1);
            }
        }
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('amfeed/category/search');
    }
}
