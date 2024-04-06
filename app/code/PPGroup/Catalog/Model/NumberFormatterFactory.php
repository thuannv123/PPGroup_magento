<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PPGroup\Catalog\Model;

class NumberFormatterFactory extends \Magento\Framework\NumberFormatterFactory
{
    public function create(array $data = [])
    {
        $numberFormatter = $this->_objectManager->create($this->_instanceName, $data);
        $numberFormatter->setPattern('#,##0.00 ¤');
        return $numberFormatter;
    }
}
