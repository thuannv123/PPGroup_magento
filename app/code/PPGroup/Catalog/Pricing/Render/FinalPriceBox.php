<?php

namespace PPGroup\Catalog\Pricing\Render;

use Magento\Framework\Pricing\Render\PriceBox as BasePriceBox;
use Magento\Msrp\Pricing\Price\MsrpPrice;

class FinalPriceBox extends \Magento\Wishlist\Pricing\Render\ConfiguredPriceBox
{
    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        $result = BasePriceBox::_toHtml();
        //Renders MSRP in case it is enabled
        if ($this->isMsrpPriceApplicable()) {
            /** @var BasePriceBox $msrpBlock */
            $msrpBlock = $this->rendererPool->createPriceRender(
                MsrpPrice::PRICE_CODE,
                $this->getSaleableItem(),
                [
                    'real_price_html' => $result,
                    'zone' => $this->getZone(),
                ]
            );
            $result = $msrpBlock->toHtml();
        }

        return $this->wrapResult($result);
    }
}
