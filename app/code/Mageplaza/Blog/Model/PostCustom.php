<?php
namespace Mageplaza\Blog\Model;

class PostCustom extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'mageplaza_blog_post_stores';

	protected $_cacheTag = 'mageplaza_blog_post_stores';

	protected $_eventPrefix = 'mageplaza_blog_post_stores';

	protected function _construct()
	{
		$this->_init('Mageplaza\Blog\Model\ResourceModel\PostCustom');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}