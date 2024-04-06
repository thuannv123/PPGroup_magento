<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\GoogleWizard;

class Shipping extends Element
{
    /**
     * @var string
     */
    protected $type = 'attribute';

    /**
     * @var string
     */
    protected $value = 'shipping';

    /**
     * @var string
     */
    protected $template = '<g:shipping>
    <g:country>::country</g:country>
    <g:price>0 ::currency</g:price>
</g:shipping>' . PHP_EOL;

    protected function getEvaluateData()
    {
        $data = parent::getEvaluateData();
        $data['::country'] = $this->direcotryData->getDefaultCountry();
        $data['::currency'] = $this->getFeed()->getFormatPriceCurrency();

        return $data;
    }
}
