<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Ui\Component\Listing\Column\Feeds\Category;

use Magento\Framework\Data\OptionSourceInterface;
use Firebear\ImportExport\Ui\Component\Form\Categories\Options as CategoryOptions;

class Magento implements OptionSourceInterface
{
    /**
     * @var CategoryOptions
     */
    protected $categoryOptions;

    /**
     * Magento constructor.
     *
     * @param CategoryOptions $categoryOptions
     */
    public function __construct(CategoryOptions $categoryOptions)
    {
        $this->categoryOptions = $categoryOptions;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        try {
            $options = $this->categoryOptions->toOptionArray();
        } catch (\Exception $exception) {
            $options = [];
        }

        return $options;
    }
}
