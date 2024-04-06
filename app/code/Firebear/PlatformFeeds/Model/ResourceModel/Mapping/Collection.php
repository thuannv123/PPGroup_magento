<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Model\ResourceModel\Mapping;

use Firebear\PlatformFeeds\Model\Mapping as MappingModel;
use Firebear\PlatformFeeds\Model\ResourceModel\Mapping as MappingResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    public function _construct()
    {
        $this->_init(MappingModel::class, MappingResourceModel::class);
    }
}
