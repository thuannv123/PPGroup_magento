<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\App\Request;
use Magento\Sales\Api\OrderRepositoryInterface;
use Acommerce\Ccpp\Gateway\Request\HtmlRedirect\OrderDataBuilder;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;


class AcceptValidator extends AbstractValidator
{
    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    private $currencyMap = [
         '840' => 'USD',
         '764' => 'THB',
         '036' => 'AUD',
         '826' => 'GBP',
         '156' => 'CNY',
         '978' => 'EUR',
         '344' => 'HKD',
         '360' => 'IDR',
         '392' => 'JPY',
         '702' => 'SGD',
         '608' => 'PHP'
     ];

     const GLUE = ":";
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

    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $paymentDO = SubjectReader::readPayment($validationSubject);

        $isValid = true;
        $fails = [];

        $statements = [
            [
                $this->checkHashValue($response), __('Authentication Failed.')
            ],
            [
                $paymentDO->getOrder()->getCurrencyCode() === $this->currencyMap[(string)$response['currency']],
                __('Currency doesn\'t match.')
            ],
            [
                str_pad($paymentDO->getOrder()->getGrandTotalAmount()*100, 12, "0", STR_PAD_LEFT)
                === $response['amount'],
                __('Amount doesn\'t match.')
            ]
        ];

        foreach ($statements as $statementResult) {
            if (!$statementResult[0]) {
                $isValid = false;
                $fails[] = $statementResult[1];
            }
        }

        return $this->createResult($isValid, $fails);
    }

    private function checkHashValue( $response)
    {
        return $this->getSignature( $response) == $response['hash_value'];
    }

    private function getSignature( $response)
    {

        $secret = $this->config->getValue('secret_key');

        $fieldsToSign =  explode(
            self::GLUE,
            (string)$this->config->getValue('response_fields')
        );

        if (!$secret || !$fieldsToSign) {
            return null;
        }

        $sign = [];
        foreach ($fieldsToSign as $field) {
            if (array_key_exists($field, $response)) {
                $sign[] = $response[$field];
            }
        }

        $strSignature = implode("", $sign);

        $HashValue = strtoupper(hash_hmac('sha1', (string)$strSignature, $secret));

        return $HashValue;
    }

}
