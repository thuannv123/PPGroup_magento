<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\ResourceModel\Eav\Model\Entity\Attribute\Option;

class Collection extends \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection
{
    public function addAttributeFilter(array $attributeCodes)
    {
        $this->join(['a' => 'eav_attribute'], 'a.attribute_id = main_table.attribute_id', ['attribute_code']);
        $this->addFieldToFilter('attribute_code', ['in' => $attributeCodes]);
        
        return $this;
    }
}
