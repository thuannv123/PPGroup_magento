<?php
namespace PPGroup\AccessTrade\Model\Api\Data;

use Magento\Framework\Api\AbstractSimpleObject;

class TrackRequest extends AbstractSimpleObject implements \PPGroup\AccessTrade\Api\Data\TrackRequestInterface {

    /**
     * @inheritDoc
     */
    public function getMcn()
    {
        return $this->_get(self::MCN);
    }

    /**
     * @inheritDoc
     */
    public function setMcn($mcn)
    {
        return $this->setData(self::MCN, $mcn);
    }

    /**
     * @inheritDoc
     */
    public function getResultId()
    {
        return $this->_get(self::RESULT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setResultId($resultId)
    {
        return $this->setData(self::RESULT_ID, $resultId);
    }

    /**
     * @inheritDoc
     */
    public function getRk()
    {
        return $this->_get(self::RK);
    }

    /**
     * @inheritDoc
     */
    public function setRk($rk)
    {
        return $this->setData(self::RK, $rk);
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier()
    {
        return $this->_get(self::IDENTIFIER);
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * @inheritDoc
     */
    public function getSalesDate()
    {
        return $this->_get(self::SALES_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setSalesDate($salesDate)
    {
        return $this->setData(self::SALES_DATE, $salesDate);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerType()
    {
        return $this->_get(self::CUSTOMER_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerType($customerType)
    {
        return $this->setData(self::CUSTOMER_TYPE, $customerType);
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->_get(self::VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getTransactionDiscount()
    {
        return $this->_get(self::TRANSACTION_DISCOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setTransactionDiscount($transactionDiscount)
    {
        return $this->setData(self::TRANSACTION_DISCOUNT, $transactionDiscount);
    }

    /**
     * @inheritDoc
     */
    public function getCurrency()
    {
        return $this->_get(self::CURRENCY);
    }

    /**
     * @inheritDoc
     */
    public function setCurrency($currency)
    {
        return $this->setData(self::CURRENCY, $currency);
    }
}
