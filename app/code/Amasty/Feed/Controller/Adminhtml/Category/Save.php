<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Controller\Adminhtml\Category;

use Amasty\Feed\Model\Category\CategoryFactory;
use Amasty\Feed\Model\Category\Repository;
use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class Save extends \Amasty\Feed\Controller\Adminhtml\AbstractCategory
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Json
     */
    private $serializer;

    public function __construct(
        Repository $repository,
        Action\Context $context,
        LoggerInterface $logger,
        Json $serializer = null // TODO move to not optional
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->logger = $logger;
        // OM for backward compatibility
        $this->serializer = $serializer ?? ObjectManager::getInstance()->get(Json::class);
    }

    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            try {
                $model = $this->repository->getCategoryEmptyEntity();
                if ($categoryId = (int)$this->getRequest()->getParam('feed_category_id')) {
                    $model = $this->repository->getById($categoryId);
                }

                if (isset($data['mapping'])) {
                    $data['mapping'] = $this->serializer->unserialize($data['mapping']);
                }
                $model->addData($data);
                $this->_session->setPageData($model->getData());
                $this->repository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the category mapping.'));
                $this->_session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $this->resultRedirectFactory->create()->setPath(
                        'amfeed/*/edit',
                        ['feed_category_id' => $model->getId()]
                    );
                }

                return $this->resultRedirectFactory->create()->setPath('amfeed/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if (!empty($categoryId)) {
                    return $this->resultRedirectFactory->create()->setPath(
                        'amfeed/*/edit',
                        ['feed_category_id' => $categoryId]
                    );
                } else {
                    return $this->resultRedirectFactory->create()->setPath('amfeed/*/new');
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the feed data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->_session->setPageData($data);

                return $this->resultRedirectFactory->create()->setPath('amfeed/*/edit', ['id' => $categoryId]);
            }
        }

        return $this->resultRedirectFactory->create()->setPath('amfeed/*/');
    }
}
