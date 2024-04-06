<?php

namespace PPGroup\Integration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PPGroup\Integration\Logger\SaleorderExportLog;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Magento\Framework\Filesystem\Io\Sftp;

class Data extends AbstractHelper
{
    const XML_PATH_INTEGRATION = 'integration/';
    protected $_objectManager = null;
    protected  $scopeConfig;
    protected $csv;
    protected $soExportLogger;
    protected $date;

    /**
     * @var Sftp
     */
    private $sftp;
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ScopeConfigInterface $appConfigScopeConfig,
        \Magento\Framework\File\Csv $csv,
        SaleorderExportLog $saleorderExportLog,
        DateTime $date,
        Sftp $sftp
    ) {
        $this->sftp = $sftp;
        $this->_objectManager = $objectManager;
        $this->scopeConfig = $appConfigScopeConfig;
        $this->csv = $csv;
        $this->soExportLogger = $saleorderExportLog;
        $this->date = $date;
    }

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_INTEGRATION . 'general/' . $code, $storeId);
    }

    public function getInventoryConfig($code, $storeId = null)
    {

        return $this->getConfigValue(self::XML_PATH_INTEGRATION . 'inventory_sync/' . $code, $storeId);
    }

    public function getSoExportConfig($code, $storeId = null)
    {

        return $this->getConfigValue(self::XML_PATH_INTEGRATION . 'sale_order_export/' . $code, $storeId);
    }

    public function getSaleOrderStatusConfig($code, $storeId = null)
    {

        return $this->getConfigValue(self::XML_PATH_INTEGRATION . 'sale_order_status/' . $code, $storeId);
    }


    public function readCSV($fileName)
    {
        $csvData = $this->csv->getData($fileName);
        array_shift($csvData);
        return $csvData;
    }

    public function createFolder($folder)
    {
        $io = $this->_objectManager->create('\Magento\Framework\Filesystem\Io\File');
        $io->checkAndCreateFolder($folder);
        $io->close();
    }

    public function moveFile($source, $destination)
    {
        $io = $this->_objectManager->create('\Magento\Framework\Filesystem\Io\File');
        $io->mv($source, $destination);
        $io->close();
    }

    public function writeCsv($filePath, $data, $delimiter = ',', $enclosure = '"')
    {
        try {
            $csvProcessor = $this->_objectManager->create('\Magento\Framework\File\Csv');
            $csvProcessor
                ->setDelimiter($delimiter)
                ->setEnclosure($enclosure)
                ->saveData(
                    $filePath,
                    $data
                );
        } catch (\Exception $exception) {
            $this->soExportLogger->info(sprintf('Exception error: %s', $exception->getMessage()));
            throw new \Exception($exception->getMessage());
        }
    }

    public function uploadFileToSftp($sftpConfig, $fileName, $source, $destination)
    {
        try {
            $this->sftp->open(
                [
                    'host' => $sftpConfig['host'],
                    'username' => $sftpConfig['username'],
                    'password' => $sftpConfig['password'],
                    'timeout'  => 300
                ]
            );

            $this->sftp->cd($destination);
            $this->sftp->write($fileName, $source);
            $this->sftp->close();

        } catch (\Exception $exception) {
            $this->soExportLogger->info(sprintf('Exception error: %s', $exception->getMessage()));
            throw new \Exception($exception->getMessage());
        }
    }

    public function saveSftpFileToLocal($sftpConfig, $filePrefix,  $sftpfilePath, $localDestination)
    {
        $listFiles = [];
        $sftp = $this->_objectManager->create('Magento\Framework\Filesystem\Io\Sftp');
        $sftp->open(
            [
                'host' => $sftpConfig['host'],
                'username' => $sftpConfig['username'],
                'password' => $sftpConfig['password']
            ]
        );

        $sftp->cd($sftpfilePath);
        $list = $sftp->rawls();
        if (is_array($list)) {
            foreach ($list as $file) {
                if (str_contains($file['filename'], $filePrefix)) {
                    $sftp->read($file['filename'], $localDestination . $file['filename']);
                    $sftp->mv($file['filename'], $sftpfilePath . 'Archive/' . $file['filename']);
                    array_push($listFiles, $file['filename']);
                }
            }
        }
        $sftp->close();
        return $listFiles;
    }

    public function convertDateToStoreTimeZone($date)
    {
        $timezone = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $orderCreated = $date;
        $orderCreated = $timezone->date(new \DateTime($orderCreated));
        return $orderCreated->format('Y-m-d H:i:s');
    }

    /**
     * function get date time from store
     * @return mixed
     */
    public function getDateTimeFromServer()
    {
        $timezone = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $dateTime = $timezone->date(new \DateTime());
        return $dateTime->format('Y-m-d_H:i:s');
    }
}
