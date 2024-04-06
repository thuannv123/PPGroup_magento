<?php
namespace Mageplaza\Blog\Model\ResourceModel;


class PostCustom extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('mageplaza_blog_post_stores', 'post_id');
	}
	
}