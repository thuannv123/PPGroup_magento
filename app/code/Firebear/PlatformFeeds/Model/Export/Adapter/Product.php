<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Model\Export\Adapter;

use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\Framework\App\CacheInterface;
use Firebear\PlatformFeeds\Feeds\Processor;
use Magento\Framework\Filesystem\File\Write as FileHandler;
use \Magento\Catalog\Model\ProductRepository;

/**
 * @codingStandardsIgnoreFile
 * phpcs:ignoreFile
 */
class Product extends AbstractAdapter
{
    /**
     * Adapter Data
     *
     * @var []
     */
    protected $data;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var FileHandler
     */
    protected $fileHandler;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Product constructor
     *
     * @param array $data
     * @inheritdoc
     */
    public function __construct(
        Filesystem $filesystem,
        CacheInterface $cache,
        Processor $processor,
        LoggerInterface $logger,
        ProductRepository $productRepository,
        $destination = null,
        array $data = []
    ) {
        register_shutdown_function([$this, 'destruct']);

        $this->cache = $cache;
        $this->processor = $processor;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->data = $data;

        parent::__construct(
            $filesystem,
            $destination
        );
    }

    /**
     * Write row data to source file.
     *
     * @throws \Exception
     * @inheritdoc
     */
    public function writeRow(array $rowData)
    {
        if ($this->processor->getTemplate() === null) {
            $this->initializeProcessor();
        }

        $rowData = $this->addFields($rowData);
        $this->processor->processRow($rowData);

        return $this;
    }

    /**
     * @param $rowData
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addFields($rowData)
    {
        $rowData['product_id'] = $this->getProductBySku($rowData['sku'])->getEntityId();

        return $rowData;
    }

    /**
     * @param $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductBySku($sku)
    {
        return $this->productRepository->get($sku);
    }

    /**
     * Initialize processor
     *
     * @throws \Exception
     */
    protected function initializeProcessor()
    {
        $this->processor->setTemplate($this->getTemplate());
    }

    /**
     * Get template
     *
     * @throws \Exception
     * @return string
     */
    protected function getTemplate()
    {
        if (empty($this->data['behavior_data']['feed_template'])) {
            throw new \Exception('Template is empty');
        }

        return $this->data['behavior_data']['feed_template'];
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getContents()
    {
        $result = $this->processor->getResult();
        $writeResult = $this->fileHandler->write($result);
        if (!$writeResult) {
            $result = '';
        }

        return $result;
    }

    /**
     * Object destructor.
     */
    public function destruct()
    {
        if (is_object($this->fileHandler)) {
            $this->fileHandler->close();
        }

        try {
            $this->_directoryHandle->delete($this->_destination);
        } catch (\Exception $exception) {
            $this->logger->warning($exception->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    protected function _init()
    {
        $this->fileHandler = $this->_directoryHandle->openFile($this->_destination, 'w');
        return $this;
    }
}
