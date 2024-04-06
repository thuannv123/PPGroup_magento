<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\ConfigInterface;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder
    ) {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        return $this->transferBuilder
            ->setClientConfig(
                [
                    'timeout' => 60,
                    'verifypeer' => 1
                ]
            )
            ->setBody($request)
            ->setMethod(\Laminas\Http\Request::METHOD_POST)
            ->setUri(
                (bool)$this->config->getValue('sandbox_flag')
                ? $this->config->getValue('iadmin_url_test')
                : $this->config->getValue('iadmin_url')
            )
            ->build();
    }
}
