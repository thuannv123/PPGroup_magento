<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Controller\Adminhtml\Mapping;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Firebear\PlatformFeeds\Model\MappingFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Firebear\PlatformFeeds\Api\MappingRepositoryInterface;

abstract class Mapping extends Action
{
    const ADMIN_RESOURCE = 'Firebear_PlatformFeeds::firebear_feeds';
    const INDEX_PAGE_URL = 'firebear_feeds/mapping/index';
    const EDIT_PAGE_URL = 'firebear_feeds/mapping/edit';

    /**
     * @var MappingFactory
     */
    protected $mappingFactory;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var MappingRepositoryInterface
     */
    protected $repository;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Mapping constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param MappingFactory $mappingFactory
     * @param SerializerInterface $serializer
     * @param MappingRepositoryInterface $repository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        MappingFactory $mappingFactory,
        SerializerInterface $serializer,
        MappingRepositoryInterface $repository
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->mappingFactory = $mappingFactory;
        $this->serializer = $serializer;
        $this->repository = $repository;
    }
}
