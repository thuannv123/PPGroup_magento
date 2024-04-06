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
 * @package     Mageplaza_OrderAttributes
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\OrderAttributes\Controller\Adminhtml\Step;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\OrderAttributes\Model\Step;
use Mageplaza\OrderAttributes\Model\StepFactory;
use RuntimeException;

/**
 * Class InlineEdit
 * @package Mageplaza\OrderAttributes\Controller\Adminhtml\Step
 */
class InlineEdit extends Action
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var StepFactory
     */
    private $stepFactory;

    /**
     * InlineEdit constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param StepFactory $attributeFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        StepFactory $attributeFactory
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->stepFactory = $attributeFactory;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $items = $this->getRequest()->getParam('items', []);

        if (empty($items) && !$this->getRequest()->getParam('isAjax')) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach ($items as $objId => $objData) {
            if (isset($objData['name']) && $objData['name'] === '') {
                return $resultJson->setData([
                    'messages' => [__('Name must not be empty.')],
                    'error' => true
                ]);
            }

            /** @var Step $object */
            $object = $this->stepFactory->create()->load($objId);

            try {
                $object->addData($objData)->save();
            } catch (RuntimeException $e) {
                $messages[] = $this->getErrorWithRuleId($object, $e->getMessage());
                $error = true;
            } catch (Exception $e) {
                $messages[] = $this->getErrorWithRuleId($object, __('Something went wrong while saving the entity.'));
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add object id to error message
     *
     * @param Step $object
     * @param string $errorText
     *
     * @return string
     */
    public function getErrorWithRuleId(Step $object, $errorText)
    {
        return '[ID: ' . $object->getId() . '] ' . $errorText;
    }
}
