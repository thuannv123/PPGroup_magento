<?php

namespace PPGroup\ZipcodeSuggestion\Model\ResourceModel\DirectoryDistrict;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            'PPGroup\ZipcodeSuggestion\Model\DirectoryDistrict',
            'PPGroup\ZipcodeSuggestion\Model\ResourceModel\DirectoryDistrict'
        );
    }
}
