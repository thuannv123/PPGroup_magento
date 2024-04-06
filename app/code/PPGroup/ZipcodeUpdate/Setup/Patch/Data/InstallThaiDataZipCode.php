<?php

namespace PPGroup\ZipcodeUpdate\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem\Driver\File;

class InstallThaiDataZipCode implements DataPatchInterface
{
    const MODULE_NAME = 'PPGroup_ZipcodeUpdate';
    const PATH_FILE_SAMPLE = '/Files/Sample/';
    const FILE_DISTRICT = 'th_district.csv';
    const FILE_SUB_DISTRICT = 'th_subdistrict.csv';
    const FILE_REGION = 'th_directory_region.csv';
    const FILE_COUNTRY_REGION = 'th_directory_country_region.csv';
    /**
     * @var Reader
     */
    protected Reader $reader;
    /**
     * @var ResourceConnection
     */
    protected ResourceConnection $resourceConnection;

    /** @var File */
    protected $file;

    /**
     * InstallThaiDataZipCode constructor.
     * @param Reader $reader
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Reader $reader,
        ResourceConnection $resourceConnection,
        File $file
    ) {
        $this->reader = $reader;
        $this->resourceConnection = $resourceConnection;
        $this->file = $file;
    }

    public function apply()
    {
        $this->deleteOldData();
        $this->addDataRegion();
        $this->addDataRegionName();
        $this->addDataDirectoryDistrict();
        $this->addDataDirectorySubDistrict();
    }

    public function getAliases()
    {
        return [];
    }

    public function deleteOldData()
    {
        $connection = $this->resourceConnection->getConnection();
        $deleteDataRegion = "DELETE FROM directory_country_region WHERE country_id = 'TH'";;
        $connection->query($deleteDataRegion);
        $deleteDataSubDistrict = "DELETE FROM directory_subdistrict";
        $connection->query($deleteDataSubDistrict);
        $deleteDataDistrict = "DELETE FROM directory_district";
        $connection->query($deleteDataDistrict);
    }

    /**
     * Add data for table region
     */
    public function addDataRegion()
    {
        $connection = $this->resourceConnection->getConnection();
        $moduleDir = $this->reader->getModuleDir('', self::MODULE_NAME);
        $file = $this->file->fileOpen($moduleDir . self::PATH_FILE_SAMPLE . self::FILE_COUNTRY_REGION, "r");

        while (($data = $this->file->fileGetCsv($file, 1000, ",")) !== false) {
            $sql = "INSERT INTO directory_country_region (region_id, country_id, code, default_name) VALUES ('" . $data[0] . "', '" . $data[1] . "', '" . $data[2] . "', '" . $data[3] . "')
            ON DUPLICATE KEY UPDATE region_id= '" . $data[0] . "', country_id='" . $data[1] . "', code='" . $data[2] . "', default_name='" . $data[3] . "'";
            $connection->query($sql);
        }
        fclose($file);
    }

     /**
     * Add data for table region
     */
    public function addDataRegionName()
    {
        $connection = $this->resourceConnection->getConnection();
        $moduleDir = $this->reader->getModuleDir('', self::MODULE_NAME);
        $file = $this->file->fileOpen($moduleDir . self::PATH_FILE_SAMPLE . self::FILE_REGION, "r");

        while (($data = $this->file->fileGetCsv($file, 1000, ",")) !== false) {
            $sqlRegionNameTH = "INSERT INTO directory_country_region_name (locale, region_id, name) VALUES ('" . $data[0] . "','" . $data[1] . "','" . $data[2] . "')
            ON DUPLICATE KEY UPDATE locale='" . $data[0] . "', region_id='" . $data[1] . "', name='" . $data[2] . "'";
            $connection->query($sqlRegionNameTH);
        }
        fclose($file);
    }

    /**
     * Add data for table directory_city
     */
    public function addDataDirectoryDistrict()
    {
        $connection = $this->resourceConnection->getConnection();
        $moduleDir = $this->reader->getModuleDir('', self::MODULE_NAME);

        $file = $this->file->fileOpen($moduleDir . self::PATH_FILE_SAMPLE . self::FILE_DISTRICT, "r");

        while (($data = $this->file->fileGetCsv($file, 1000, ",")) !== false) {
            $sql = "INSERT INTO directory_district (district_id, region_id, country_id, name, th_name) VALUES ('" . $data[0] . "', '" . $data[1] . "', 'TH', '" . $data[3] . "', '". $data[4] ."') 
            ON DUPLICATE KEY UPDATE district_id='" . $data[0] . "', region_id='" . $data[1] . "', country_id='TH', name='" . $data[3] . "', th_name='" . $data[4] . "'";
            $connection->query($sql);
        }
        fclose($file);
    }

    /**
     * Add data for table directory_town
     */
    public function addDataDirectorySubDistrict()
    {
        $connection = $this->resourceConnection->getConnection();
        $moduleDir = $this->reader->getModuleDir('', self::MODULE_NAME);

        $file = $this->file->fileOpen($moduleDir . self::PATH_FILE_SAMPLE . self::FILE_SUB_DISTRICT, "r");

        while (($data = $this->file->fileGetCsv($file, 1000, ",")) !== false) {
            $zipcode = $data[2];
            $sql = "INSERT INTO directory_subdistrict (district_id, zipcode , name, th_name) VALUES ('" . $data[1] . "', '" . $zipcode . "','" . $data[3] . "','" . $data[4] . "')
            ON DUPLICATE KEY UPDATE district_id='" . $data[1] . "', zipcode='" . $zipcode . "' , name='" . $data[3] . "', th_name='" . $data[4] . "'";
            $connection->query($sql);
        }
        fclose($file);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
