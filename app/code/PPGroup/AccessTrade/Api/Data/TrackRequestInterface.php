<?php

namespace PPGroup\AccessTrade\Api\Data;

/**
 * @api
 */
interface TrackRequestInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const MCN = 'mcn';
    const RESULT_ID = 'result_id';
    const RK = 'rk';
    const IDENTIFIER = 'identifier';
    const SALES_DATE = 'sales_date';
    const CUSTOMER_TYPE = 'customer_type';
    const VALUE = 'value';
    const TRANSACTION_DISCOUNT = 'transaction_discount';
    const CURRENCY = 'currency';

    /**
     * Get Mcn
     *
     * @return string
     */
    public function getMcn();

    /**
     * Set Mcn
     *
     * @param $mcn
     * @return \PPGroup\AccessTrade\Api\Data\TrackRequestInterface
     */
    public function setMcn($mcn);

    /**
     * Get Result Id
     *
     * @return string
     */
    public function getResultId();

    /**
     * Set Result Id
     *
     * @param $resultId
     * @return \PPGroup\AccessTrade\Api\Data\TrackRequestInterface
     */
    public function setResultId($resultId);

    /**
     * Get Rk
     *
     * @return string
     */
    public function getRk();

    /**
     * @param $rk
     * @return \PPGroup\AccessTrade\Api\Data\TrackRequestInterface
     */
    public function setRk($rk);

    /**
     * Get Identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Set identifier
     *
     * @param $identifier
     * @return \PPGroup\AccessTrade\Api\Data\TrackRequestInterface
     */
    public function setIdentifier($identifier);

    /**
     * Get Sales Date
     *
     * @return string
     */
    public function getSalesDate();

    /**
     * Set Sales Date
     *
     * @param $salesDate
     * @return \PPGroup\AccessTrade\Api\Data\TrackRequestInterface
     */
    public function setSalesDate($salesDate);

    /**
     * Get Customer Type
     *
     * @return string
     */
    public function getCustomerType();

    /**
     * Set Customer Type
     * @param $customerType
     * @return \PPGroup\AccessTrade\Api\Data\TrackRequestInterface
     */
    public function setCustomerType($customerType);

    /**
     * @return float
     */
    public function getValue();

    /**
     * Set Value
     * @param $value
     * @return \PPGroup\AccessTrade\Api\Data\TrackRequestInterface
     */
    public function setValue($value);

    /**
     * Get Transaction Discount
     *
     * @return float
     */
    public function getTransactionDiscount();

    /**
     * Set Transaction Discount
     *
     * @param $transactionDiscount
     * @return \PPGroup\AccessTrade\Api\Data\TrackRequestInterface
     */
    public function setTransactionDiscount($transactionDiscount);

    /**
     * Get Currency Code
     *
     * @return string
     */
    public function getCurrency();

    /**
     * Set Currency Code
     *
     * @param $currency
     * @return \PPGroup\AccessTrade\Api\Data\TrackRequestInterface
     */
    public function setCurrency($currency);
}
