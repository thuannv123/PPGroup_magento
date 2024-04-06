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

namespace Mageplaza\OrderAttributes\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\FormData;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\OrderAttributes\Helper\Data;
use Mageplaza\OrderAttributes\Model\StepFactory;

/**
 * Class Step
 * @package Mageplaza\OrderAttributes\Controller\Adminhtml
 */
abstract class Step extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Mageplaza_OrderAttributes::step';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var StepFactory
     */
    protected $_stepFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var FormData
     */
    protected $formData;

    /**
     * Attribute constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param StepFactory $attrFactory
     * @param Data $helperData
     * @param FormData $formData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        StepFactory $attrFactory,
        Data $helperData,
        FormData $formData
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->_stepFactory = $attrFactory;
        $this->helperData = $helperData;
        $this->formData = $formData;

        parent::__construct($context);
    }

    /**
     * Load layout, set breadcrumbs
     *
     * @return Page
     */
    protected function _initAction()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);

        return $resultPage;
    }

    /**
     * @return \Mageplaza\OrderAttributes\Model\Step
     */
    protected function _initStep()
    {
        return $this->_stepFactory->create();
    }

    /**
     * @param string $path
     * @param array $params
     *
     * @return Redirect
     */
    protected function returnResult($path = '', array $params = ['_current' => true])
    {
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($path, $params);
    }
}
