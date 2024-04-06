<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\CurlProxy\HTTP\Client;


class Curl extends \Magento\Framework\HTTP\Client\Curl
{

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;


    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }



    /**
     * Make request
     * @param string $method
     * @param string $uri
     * @param array $params
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function makeRequest($method, $uri, $params = [])
    {

        if(!$this->_scopeConfig->getValue('curl_proxy/curl_proxy_setup/proxy_enabled')) {
            $args = func_get_args();
            return call_user_func_array([$this,'parent::makeRequest'],$args);
        }

        $proxyUrl = $this->_scopeConfig->getValue('curl_proxy/curl_proxy_setup/proxy_url');

        $this->_ch = curl_init();

        $this->curlOption(CURLOPT_PROXY, $proxyUrl);
        $this->curlOption(CURLOPT_SSL_VERIFYPEER, false);
        $this->curlOption(CURLOPT_URL, $uri);


        if ($method == 'POST') {
            $this->curlOption(CURLOPT_POST, 1);

            // for cpms they want to encode authen
            if(isset($params['isEncode']) && $params['isEncode']){
                $this->curlOption(CURLOPT_POSTFIELDS, json_encode($params['authen']));
            } else {
                $this->curlOption(CURLOPT_POSTFIELDS, http_build_query($params));
            }

        } elseif ($method == "GET") {
            $this->curlOption(CURLOPT_HTTPGET, 1);
        } else {
            $this->curlOption(CURLOPT_CUSTOMREQUEST, $method);
        }


        if (count($this->_headers)) {
            $heads = [];
            foreach ($this->_headers as $k => $v) {
                $heads[] = $k . ': ' . $v;
            }
            $this->curlOption(CURLOPT_HTTPHEADER, $heads);
        }



        if (count($this->_cookies)) {
            $cookies = [];
            foreach ($this->_cookies as $k => $v) {
                $cookies[] = "{$k}={$v}";
            }
            $this->curlOption(CURLOPT_COOKIE, implode(";", $cookies));
        }

        if ($this->_timeout) {
            $this->curlOption(CURLOPT_TIMEOUT, $this->_timeout);
        }

        if ($this->_port != 80) {
            $this->curlOption(CURLOPT_PORT, $this->_port);
        }

        //$this->curlOption(CURLOPT_HEADER, 1);
        $this->curlOption(CURLOPT_RETURNTRANSFER, 1);
        $this->curlOption(CURLOPT_HEADERFUNCTION, [$this, 'parseHeaders']);

        if (count($this->_curlUserOptions)) {
            foreach ($this->_curlUserOptions as $k => $v) {
                $this->curlOption($k, $v);
            }
        }

        $this->_headerCount = 0;
        $this->_responseHeaders = [];
        $this->_responseBody = curl_exec($this->_ch);

        $err = curl_errno($this->_ch);
        if ($err) {
            $this->doError(curl_error($this->_ch));
        }
        curl_close($this->_ch);
    }
}
