<?php

namespace PPGroup\ZipcodeUpdate\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Api\AddressRepositoryInterface;

class UpdateCustomerRegionId implements DataPatchInterface
{
    /**
     * @var ResourceConnection
     */
    protected ResourceConnection $resourceConnection;
    /**
     * @var AddressRepositoryInterface
     */
    protected AddressRepositoryInterface $addressRepository;

    /**
     * UpdateThaiDataZipCode constructor.
     * @param ResourceConnection $resourceConnection
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->addressRepository = $addressRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $connection = $this->resourceConnection->getConnection();
        $zipcodeSql = "SELECT DISTINCT postcode, region FROM customer_address_entity";
        $listAddressZipcodes = $connection->fetchAll($zipcodeSql);
        foreach($listAddressZipcodes as $data){
            if(is_numeric($data['postcode'])){
                $regionIdSql = "SELECT DISTINCT `directory_district`.`region_id`
                                FROM `directory_district`
                                INNER JOIN `directory_country_region`
                                ON `directory_district`.`region_id` = `directory_country_region`.`region_id`
                                AND `directory_country_region`.`default_name` = '" . $data['region'] . "'
                                INNER JOIN `directory_subdistrict`
                                ON `directory_district`.`district_id` = `directory_subdistrict`.`district_id`
                                AND `directory_subdistrict`.`zipcode` = " . $data['postcode'] . "";
                $listRegionId = $connection->fetchAll($regionIdSql);
                foreach($listRegionId as $region){
                    $sql = "UPDATE `customer_address_entity` SET `region_id` = " .$region['region_id']. "
                    WHERE `customer_address_entity`.`postcode`= " . $data['postcode'] ."
                    AND `customer_address_entity`.`region` = '" . $data['region'] . "'";
                    $connection->query($sql);
                }
                
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
