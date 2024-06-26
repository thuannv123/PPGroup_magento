<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package FAQ and Product Questions for Magento 2
 */

namespace Amasty\Faq\Controller\Adminhtml\Question;

use Amasty\Faq\Api\QuestionRepositoryInterface;
use Amasty\Faq\Model\QuestionFactory;
use Amasty\Faq\Model\ResourceModel\Tag\CollectionFactory;
use Amasty\Faq\Model\TagFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Amasty\Faq\Controller\Adminhtml\AbstractQuestion
{
    /**
     * @var QuestionRepositoryInterface
     */
    private $repository;

    /**
     * @var QuestionFactory
     */
    private $questionFactory;

    /**
     * @var DataObject
     */
    private $associatedQuestionEntityMap;

    /**
     * @var CollectionFactory
     */
    private $tagCollectionFactory;

    /**
     * @var TagFactory
     */
    private $tagFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(
        Context $context,
        QuestionRepositoryInterface $repository,
        QuestionFactory $questionFactory,
        DataObject $associatedQuestionEntityMap,
        CollectionFactory $tagCollectionFactory,
        TagFactory $tagFactory,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->questionFactory = $questionFactory;
        $this->associatedQuestionEntityMap = $associatedQuestionEntityMap;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->tagFactory = $tagFactory;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * Save Action
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->questionFactory->create();
                $data = $this->getRequest()->getPostValue();

                $data['product_ids'] = [];
                if (!empty($data['products'])) {
                    /** collect Product Ids wich assigned to current Question */
                    foreach ($data['products'] as $productData) {
                        $data['product_ids'][] = (int) $productData['entity_id'];
                    }
                }

                foreach ($this->getReferenceConfig() as $entityType => $referenceConfig) {
                    if (!isset($data[$entityType])) {
                        $data[$entityType] = [];
                    }
                }

                $data['tags'] = [];
                if (!empty($data['tag_titles'])) {
                    $data = $this->prepareData($data);
                }
                $model->addData($data);
                $this->repository->save($model);

                if ($this->getRequest()->getParam('save_and_send')) {
                    if (!empty($model->getEmail())) {
                        $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
                        $result->setParams(['id' => $model->getId()]);

                        return $result->forward('send');
                    }
                    $this->messageManager->addWarningMessage(__('Email can not be sent. Email field is empty.'));
                }

                $this->messageManager->addSuccessMessage(__('You saved the item.'));

                if ($this->getRequest()->getParam('back')) {
                    return $this->resultRedirectFactory->create()->setPath('*/*/edit', ['id' => $model->getId()]);
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->dataPersistor->set('questionData', $data);

                $resultRedirect = $this->resultRedirectFactory->create();
                if ($questionId = (int)$this->getRequest()->getParam('question_id')) {
                    $resultRedirect->setPath('*/*/edit', ['id' => $questionId]);
                } else {
                    $resultRedirect->setPath('*/*/new');
                }

                return $resultRedirect;
            }
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }

    /**
     * @param array $data
     * @return array $data
     */
    private function prepareData($data)
    {
        $questionTitles = explode(',', $data['tag_titles']);
        $data['tags'] = [];
        $tagCollection = $this->tagCollectionFactory->create()->addFieldToFilter('title', ['in' => $questionTitles]);
        foreach ($questionTitles as $tagTitle) {
            $tagTitle = strip_tags((string) $tagTitle);
            if (!$tagCollection->getItemByColumnValue('title', $tagTitle)) {
                $tagModel = $this->tagFactory->create();
                $tagModel->setTitle($tagTitle);
                $data['tags'][] = $tagModel;
            } else {
                $data['tags'][] = $tagCollection->getItemByColumnValue('title', $tagTitle);
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getReferenceConfig()
    {
        return $this->associatedQuestionEntityMap->getData();
    }
}
