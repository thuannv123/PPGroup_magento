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
namespace Mageplaza\OrderAttributes\Controller\Adminhtml\Viewfile;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\File\Uploader;
use Mageplaza\OrderAttributes\Helper\Data;

/**
 * Class File
 * @package Mageplaza\OrderAttributes\Controller\Adminhtml\Viewfile
 */
class File extends \Mageplaza\OrderAttributes\Controller\Viewfile\Index
{

    /**
     * @return ResponseInterface|Raw|ResultInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        $fileName = $this->getRequest()->getParam('f');
        $fileName = Uploader::getDispersionPath($fileName) . '/' . $fileName;
        $fileName = Data::TEMPLATE_MEDIA_PATH . '/' . ltrim($fileName, '/');
        $path     = $this->getDirectoryReadMedia()->getAbsolutePath($fileName);
        if (mb_strpos($path, '..') !== false
            || (!$this->getDirectoryReadMedia()->isFile($fileName) && !$this->storage->processStorageFile($path))
        ) {
            throw new NotFoundException(__('Page not found.'));
        }

        $name = pathinfo($path, PATHINFO_BASENAME);

        return $this->fileFactory->create(
            $name,
            ['type' => 'filename', 'value' => $fileName],
            DirectoryList::MEDIA
        );
    }
}
