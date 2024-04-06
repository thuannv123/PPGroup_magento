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

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\FormData;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\OrderAttributes\Controller\Adminhtml\Step;
use Mageplaza\OrderAttributes\Helper\Data;
use Mageplaza\OrderAttributes\Model\StepFactory;

/**
 * Class MassDelete
 * @package Mageplaza\OrderAttributes\Controller\Adminhtml\Step
 */
class MassDelete extends Step
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * MassDelete constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param StepFactory $attrFactory
     * @param Data $helperData
     * @param FormData $formData
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        StepFactory $attrFactory,
        Data $helperData,
        FormData $formData,
        Filter $filter
    ) {
        $this->filter = $filter;
        parent::__construct($context, $resultPageFactory, $coreRegistry, $attrFactory, $helperData, $formData);
    }

    /**
     * @return Redirect|ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {

        $collection = $this->filter->getCollection($this->_initStep()->getCollection());
        $deleted    = 0;
        foreach ($collection->getItems() as $form) {
            $form->delete();
            $deleted++;
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $deleted)
        );

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
