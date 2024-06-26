<?php

namespace PPGroup\ZipcodeSuggestion\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class DirectorySubdistrict extends AbstractDb
{


    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('directory_subdistrict', 'subdistrict_id');
    }
}
