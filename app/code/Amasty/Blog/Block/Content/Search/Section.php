<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Content\Search;

use Amasty\Blog\Block\Content\Lists\Pager;
use Amasty\Blog\Helper\Settings;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Section extends Template
{
    /**
     * @var Settings
     */
    private $settings;

    public function __construct(
        Settings $settings,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->settings = $settings;
    }

    public function getCollection(): ?AbstractCollection
    {
        return $this->getData('collection');
    }

    /**
     * @throws LocalizedException
     */
    public function getToolbar(bool $isAmp = false): Pager
    {
        /** @var Pager $toolbar */
        $toolbar = $this->getLayout()->createBlock(Pager::class);
        $template = $isAmp ? 'Amasty_Blog::amp/list/pager.phtml' : 'Amasty_Blog::list/pager.phtml';
        $toolbar->setTemplate($template);
        $toolbar->setPageVarName($this->getData('entityName') . '_page');
        $toolbar->setLimitVarName($this->getData('entityName') . '_limit');

        if ($this->getData('entityName') === 'posts') {
            $toolbar->setLimit($this->settings->getPostsLimit());
        }

        $toolbar->setCollection($this->getCollection());
        $toolbar->setIsMultiSearch(true);
        $toolbar->setSearchPage(true);

        return $toolbar;
    }
}
