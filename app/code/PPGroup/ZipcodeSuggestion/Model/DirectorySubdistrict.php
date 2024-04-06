<?php

namespace PPGroup\ZipcodeSuggestion\Model;

use \Magento\Framework\Model\AbstractModel;

class DirectorySubdistrict extends AbstractModel
{


    /**
     * Initialize resource model
     * @return void
     */
    public function _construct()
    {
        $this->_init('PPGroup\ZipcodeSuggestion\Model\ResourceModel\DirectorySubdistrict');
    }
}
