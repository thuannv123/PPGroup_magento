<?php

namespace PPGroup\Blog\Helper;

use Exception;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;

class Data extends AbstractData
{
    /**
     * @var Json
     */
    protected $json;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Json $json
     *
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Json $json
    ) {
        $this->json = $json;
        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param array $value
     *
     * @return bool|false|string
     */
    public function jsonEncodeData($value)
    {
        try {
            return $this->json->serialize($value);
        } catch (Exception $e) {
            return '{}';
        }
    }

    /**
     * @param string $value
     *
     * @return array
     */
    public function jsonDecodeData($value)
    {
        try {
            return $this->json->unserialize($value);
        } catch (Exception $e) {
            return [];
        }
    }
}
