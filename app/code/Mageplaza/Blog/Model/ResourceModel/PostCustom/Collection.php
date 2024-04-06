<?php
namespace Mageplaza\Blog\Model\ResourceModel\PostCustom;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\Select;
use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;
use Mageplaza\Blog\Api\Data\SearchResult\CategorySearchResultInterface;

class Collection extends AbstractCollection
{
	protected $_idFieldName = 'post_id';
	protected $_eventPrefix = 'mageplaza_blog_post_stores_collection';
	protected $_eventObject = 'post_stores_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Mageplaza\Blog\Model\PostCustom', 'Mageplaza\Blog\Model\ResourceModel\PostCustom');
	}

	
    public function addAttributeToFilter($field, $condition = null)
    {
        return $this->addFieldToFilter($field, $condition);
    }

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @return $this
     */
    public function setProductStoreId()
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function setLoadProductCount()
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function setStoreId()
    {
        return $this;
    }

    /**
     * @param string $attribute
     * @param bool $joinType
     *
     * @return $this
     */
    public function addAttributeToSelect($attribute, $joinType = false)
    {
        return $this;
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Select::GROUP);

        return $countSelect;
    }

    /**
     * @param null $valueField
     * @param string $labelField
     * @param array $additional
     *
     * @return array
     */
    protected function _toOptionArray($valueField = null, $labelField = 'name', $additional = [])
    {
        $valueField = 'post_id';

        return parent::_toOptionArray($valueField, $labelField, $additional); // TODO: Change the autogenerated stub
    }

    /**
     * Add if filter
     *
     * @param array|mixed $categoryIds
     *
     * @return $this
     */
}