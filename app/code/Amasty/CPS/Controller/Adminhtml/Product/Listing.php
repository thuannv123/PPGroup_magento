<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Product Sorting for Shop by Brand (Add-On) for Magento 2
 */

namespace Amasty\CPS\Controller\Adminhtml\Product;

class Listing extends ControllerAbstract
{
    /**
     * @return string
     */
    public function execute()
    {
        $block = $this->layoutFactory->create()->createBlock(
            \Amasty\CPS\Block\Adminhtml\Products\Listing::class,
            'product.listing'
        );

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents(
            $block->toHtml()
        );
    }
}
