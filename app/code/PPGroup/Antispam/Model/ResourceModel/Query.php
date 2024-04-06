<?php
/**
 * Author: Son Nguyen
 * Copyright © Wiki Solution All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PPGroup\Antispam\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use PPGroup\Antispam\Helper\Data;
use Magento\Search\Model\Query as QueryModel;
use Zend_Db_Expr;

/**
 * Search query resource model
 * @api
 * @since 100.0.2
 */
class Query extends \Magento\Search\Model\ResourceModel\Query
{

    protected Data $_data;

    public function __construct(
        Context           $context,
        DateTime\DateTime $date,
        DateTime          $dateTime,
        Data              $data,
                          $connectionName = null
    )
    {
        $this->_data = $data;
        parent::__construct($context, $date, $dateTime, $connectionName);
    }

    /**
     * Save query with incremental popularity
     *
     * @param QueryModel $query
     * @return void
     *
     * @throws LocalizedException
     */
    public function saveIncrementalPopularity(QueryModel $query)
    {
        if ($this->_data->checkSpam($query->getQueryText())) {
            $adapter = $this->getConnection();
            $table = $this->getMainTable();
            $saveData = [
                'store_id' => $query->getStoreId(),
                'query_text' => $query->getQueryText(),
                'popularity' => 1,
            ];
            $updateData = [
                'popularity' => new Zend_Db_Expr('`popularity` + 1'),
            ];
            $adapter->insertOnDuplicate($table, $saveData, $updateData);
        }
    }

    /**
     * Save query with number of results
     *
     * @param QueryModel $query
     * @return void
     *
     * @throws LocalizedException
     */
    public function saveNumResults(QueryModel $query)
    {
        if ($this->_data->checkSpam($query->getQueryText())) {
            $adapter = $this->getConnection();
            $table = $this->getMainTable();
            $numResults = $query->getNumResults();
            $saveData = [
                'store_id' => $query->getStoreId(),
                'query_text' => $query->getQueryText(),
                'num_results' => $numResults,
            ];
            $updateData = ['num_results' => $numResults];
            $adapter->insertOnDuplicate($table, $saveData, $updateData);
        }
    }
}
