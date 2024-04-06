<?php

namespace PPGroup\AccessTrade\ViewModel;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use PPGroup\AccessTrade\Config\Config;
use Magento\Store\Model\StoreManagerInterface;

class Common implements ArgumentInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Config $config
     * @param SerializerInterface $serializer
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        SerializerInterface $serializer,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
    }

    public function getConfig(): array
    {
        return [
            'enabled' => $this->config->isEnabled(),
            'storeCode' => $this->storeManager->getStore()->getCode()
        ];
    }

    public function getSerializedConfig(): string
    {
        return $this->serializer->serialize($this->getConfig());
    }
}
