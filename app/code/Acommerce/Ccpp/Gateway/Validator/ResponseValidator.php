<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Gateway\Validator;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Framework\App\Request;
use Magento\Sales\Api\OrderRepositoryInterface;
use Acommerce\Ccpp\Gateway\Request\HtmlRedirect\OrderDataBuilder;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Helper\SubjectReader;

class ResponseValidator extends AbstractValidator
{
    /**
     * @var Request\Http
     */
    private $request;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param Request\Http $request
     * @param RemoteAddress $remoteAddress
     * @param ConfigInterface $config
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        Request\Http $request,
        RemoteAddress $remoteAddress,
        ConfigInterface $config,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($resultFactory);

        $this->request = $request;
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->remoteAddress = $remoteAddress;
    }
    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(array $validationSubject)
    {
        if (!$this->request->isPost()) {
            return $this->createResult(false, [__("Wrong request type.")]);
        }

        return $this->createResult(true);
    }

}
