<?php

namespace PPGroup\AccessTrade\Model;

use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use PPGroup\AccessTrade\Config\Config;
use Psr\Log\LoggerInterface;

class Client
{
    const SWITCHING_PROTOCOLS = 101;
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NONAUTHORITATIVE_INFORMATION = 203;
    const NO_CONTENT = 204;
    const RESET_CONTENT = 205;
    const PARTIAL_CONTENT = 206;
    const MULTIPLE_CHOICES = 300;
    const MOVED_PERMANENTLY = 301;
    const MOVED_TEMPORARILY = 302;
    const SEE_OTHER = 303;
    const NOT_MODIFIED = 304;
    const USE_PROXY = 305;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const PROXY_AUTHENTICATION_REQUIRED = 407;
    const REQUEST_TIMEOUT = 408;
    const CONFLICT = 408;
    const GONE = 410;
    const LENGTH_REQUIRED = 411;
    const PRECONDITION_FAILED = 412;
    const REQUEST_ENTITY_TOO_LARGE = 413;
    const REQUESTURI_TOO_LARGE = 414;
    const UNSUPPORTED_MEDIA_TYPE = 415;
    const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const EXPECTATION_FAILED = 417;
    const IM_A_TEAPOT = 418;
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;
    const SERVICE_UNAVAILABLE = 503;
    const GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * @var ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var System\Dumper
     */
    protected $dumper;

    /**
     * @var array
     */
    private $logData = [];

    /**
     * @param ZendClientFactory $httpClientFactory
     * @param Config $config
     * @param LoggerInterface $logger
     * @param System\Dumper $dumper
     */
    public function __construct(
        ZendClientFactory $httpClientFactory,
        Config $config,
        LoggerInterface $logger,
        System\Dumper $dumper
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->config = $config;
        $this->logger = $logger;
        $this->dumper = $dumper;
    }

    /**
     * @param $request
     * @return array
     * @throws \Exception
     */
    public function postRequest(&$request)
    {
        $data = [];
        $this->logData['request'] = $request;
        $requestType = $request['method_type'];

        if (isset($request['method_type'])) {
            unset($request['method_type']);
        }

        if (!in_array($requestType, ['GET', 'POST', 'PUT', 'DELETE'])) {
            throw new \Exception('Send first parameter must be "GET", "POST", "PUT" or "DELETE"');
        }

        $url = $this->config->getApiUrl();

        try {
            /** @var ZendClient $client */
            $client = $this->httpClientFactory->create();

            $client->setUri($url);
            $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
            $client->setMethod($requestType);
            if ($requestType == 'GET') {
                $client->setParameterGet($request);
            } elseif ($requestType == 'POST') {
                $client->setParameterPost($request);
            }
            $responseBody = $client->request()
                ->getBody();
            $responseStatus = $client->request()->getStatus();
            $this->logData  = array_merge($this->logData, ['status' => $responseStatus, 'response'=> $responseBody]);
            $data['status'] = $responseStatus == self::ACCEPTED || $responseStatus == self::OK;
        } catch (\InvalidArgumentException $e) {
            $data['status'] = false;
            $this->logData  = array_merge($this->logData, ['status' => false, 'message'=> $e->getMessage()]);
        } finally {
            if ($this->config->isDebugMode()) {
                $this->logger->info($this->info($this->logData));
            }
        }

        return $data;

    }

    /**
     * @param $message
     * @return mixed|string
     */
    public function info($message)
    {
        if (is_array($message)) {
            $message = $this->dumper->dump($message, 5);
        }

        return $message;
    }
}
