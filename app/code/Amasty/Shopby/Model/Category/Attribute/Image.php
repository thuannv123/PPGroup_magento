<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Category\Attribute;

use Magento\Catalog\Model\Category as CategoryModel;
use Amasty\Shopby\Model\Media\ImageProcessor;

class Image
{
    /**
     * @var ImageProcessor
     */
    protected $imageProcessor;

    /**
     * Image constructor.
     * @param ImageProcessor $imageProcessor
     */
    public function __construct(
        ImageProcessor $imageProcessor
    ) {
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @param string $imageAttributeCode
     * @param CategoryModel $category
     * @return void
     */
    protected function processImage($imageAttributeCode, CategoryModel $category)
    {
        $rawData = $category->getData($imageAttributeCode);

        if (!$rawData) {
            $category->setData($imageAttributeCode, null);
        }

        if ($rawData && is_array($rawData)) {
            if (isset($rawData[0]['file']) && isset($rawData[0]['url'])) {
                $category->setData($imageAttributeCode, $rawData[0]['file']);
                $this->imageProcessor->resize($rawData[0]['file'], $imageAttributeCode);
            } else {
                $category->unsetData($imageAttributeCode);
            }
        }
    }
}
