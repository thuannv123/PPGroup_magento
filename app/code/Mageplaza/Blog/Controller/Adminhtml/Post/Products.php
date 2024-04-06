<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Mageplaza\Blog\Controller\Adminhtml\Post;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\PostHistoryFactory;

/**
 * Class Products
 * @package Mageplaza\Blog\Controller\Adminhtml\Post
 */
class Products extends Post
{
    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var PostHistoryFactory
     */
    protected $postHistoryFactory;

    /**
     * Products constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param PostFactory $productFactory
     * @param PostHistoryFactory $postHistoryFactory
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PostFactory $productFactory,
        PostHistoryFactory $postHistoryFactory,
        LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($productFactory, $registry, $context);

        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->postHistoryFactory = $postHistoryFactory;
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('history')) {
            $history = $this->postHistoryFactory->create()->load($this->getRequest()->getParam('id'));
            $this->coreRegistry->register('mageplaza_blog_post', $history);
        } else {
            $this->initPost(true);
        }

        return $this->resultLayoutFactory->create();
    }
}
