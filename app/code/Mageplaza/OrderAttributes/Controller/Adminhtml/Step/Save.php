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
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\FormData;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Mageplaza\OrderAttributes\Controller\Adminhtml\Step;
use Mageplaza\OrderAttributes\Helper\Data;
use Mageplaza\OrderAttributes\Model\Config\Source\IconType;
use Mageplaza\OrderAttributes\Model\Step as StepModel;
use Mageplaza\OrderAttributes\Model\StepFactory;

/**
 * Class Save
 * @package Mageplaza\OrderAttributes\Controller\Adminhtml\Step
 */
class Save extends Step
{
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;
    /**
     * @var UploaderFactory
     */
    protected $_fileUploaderFactory;
    /**
     * @var Filesystem
     */
    protected $fileSystem;
    /**
     * @var File
     */
    protected $file;

    /**
     * @var DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param StepFactory $attrFactory
     * @param Data $helperData
     * @param FormData $formData
     * @param Filesystem $filesystem
     * @param UploaderFactory $fileUploaderFactory
     * @param File $file
     * @param DateTimeFactory $dateTimeFactory
     *
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        StepFactory $attrFactory,
        Data $helperData,
        FormData $formData,
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory,
        File $file,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->_mediaDirectory      = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->fileSystem           = $filesystem;
        $this->file                 = $file;
        $this->dateTimeFactory      = $dateTimeFactory;

        parent::__construct($context, $resultPageFactory, $coreRegistry, $attrFactory, $helperData, $formData);
    }

    /**
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $data   = $this->getRequest()->getPostValue();
        $stepId = $this->getRequest()->getParam('id');

        if (!$data) {
            return $this->returnResult('mporderattributes/*/', []);
        }

        $formObj = $this->_initStep();
        $formObj->load($stepId);
        if (!$formObj->getId() && $stepId) {
            $this->messageManager->addErrorMessage(__('This form does not exist.'));
            $this->_redirect('*/*/');
        }

        try {
            $data['step_id']        = $stepId;
            $data['customer_group'] = implode(',', $data['customer_group'] ?: '');
            $data['store_id']       = implode(',', $data['store_id'] ?: '');
            $data['conditions']     = $data['rule']['conditions'];
            unset($data['rule']);
            $data['updated_at']     = $this->getCurrentGMTDateTime();
            $formObj->loadPost($data);
            if ((int) $data['icon_type'] === IconType::CUSTOM) {
                $this->uploadIcon($data, $formObj->getIconCustom());
            }
            $this->validateBeforeSave($data);
            $formObj->addData($data)->save();

            $stores = $this->helperData->getStores();
            foreach ($stores as $store) {
                if ($store->getIsActive()) {
                    $this->helperData->createJsFileStep(
                        $formObj,
                        $this->helperData->getLocaleCode($store->getId()),
                        'back_end'
                    );
                }
            }
            $this->messageManager->addSuccessMessage(__('You saved the Form.'));
            if ($this->getRequest()->getParam('back')) {
                return $this->_redirect('*/step/edit', ['id' => $formObj->getId()]);
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving form.'));

            return $this->_redirect('*/form/edit', ['id' => $formObj->getId()]);
        }

        return $this->returnResult('mporderattributes/step/*', []);
    }

    /**
     * Upload Icon of Step to show in Osc
     *
     * @param $data
     * @param $iconOld
     *
     * @throws FileSystemException
     * @throws Exception
     */
    public function uploadIcon(&$data, $iconOld)
    {
        if (isset($data['icon_type_custom']['delete']) && $data['icon_type_custom']['delete']) {
            $data['icon_type_custom'] = '';
            $this->deleteIcon($iconOld);

            return;
        }
        $file = $this->getRequest()->getFiles()->toArray();
        if (isset($file['icon_type_custom']) && $file['icon_type_custom']['size']) {
            $target   = $this->_mediaDirectory->getAbsolutePath(StepModel::ICON_IMG_MEDIA_PATH);
            $uploader = $this->_fileUploaderFactory->create(['fileId' => 'icon_type_custom']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->save($target);
            if ($uploader->getUploadedFileName()) {
                $data['icon_type_custom'] = $uploader->getUploadedFileName();
            } else {
                $data['icon_type_custom'] = '';
            }
        }
    }

    /**
     * @param $icon
     *
     * @throws FileSystemException
     */
    public function deleteIcon($icon)
    {
        $mediaRootDir = $this->fileSystem
            ->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        if (!is_string($icon) && is_array($icon)) {
            $icon = $icon['value'];
        }
        if ($this->file->isExists($mediaRootDir . $icon)) {
            $this->file->deleteFile($mediaRootDir . $icon);
        }
    }

    /**
     * @param $data
     */
    public function validateBeforeSave(&$data)
    {
        if (isset($data['icon_type_custom']) && !is_string($data['icon_type_custom'])
            && is_array($data['icon_type_custom']) && isset($data['icon_type_custom']['value'])) {
            $data['icon_type_custom'] = $data['icon_type_custom']['value'];
        }
        if (isset($data['icon_type_custom']) && $data['icon_type_custom']
            && !str_contains($data['icon_type_custom'], StepModel::ICON_IMG_MEDIA_PATH)) {
            $data['icon_type_custom'] = StepModel::ICON_IMG_MEDIA_PATH . $data['icon_type_custom'];
        }
    }

    /**
     * @return false|string
     */
    public function getCurrentGMTDateTime()
    {
        $dateModel = $this->dateTimeFactory->create();
        return $dateModel->gmtDate();
    }
}
