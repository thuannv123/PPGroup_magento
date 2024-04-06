<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 * PHP version 5
 *
 * @category Acommerce_Ccpp
 * @package  Acommerce
 * @author   Ranai L <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */

namespace Acommerce\Ccpp\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\App\Config\BaseFactory as ConfigFactory;

/**
 * Ccpp payment Helper
 *
 * @category Acommerce_Ccpp
 * @package  Acommerce
 * @author   Ranai L <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.acommerce.asia
 */
class Data extends AbstractHelper
{

    /**
     * Grandtotal number of digits
     *
     * @var number
     */
    const ALLOW_AMOUNT_NUMBER = 12;

    const CURRENCY_CODE = 'THB';

    const COUNTRY_CODE = 'THA';

    const XML_PATH_CONFIG = 'checkout/confirm_email/enabled';

    /**
     * Store Manage
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Scope Config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Url Interface
     *
     * @var UrlInterface
     */
    protected $urlInterface;


    protected $dateTime;


    protected $timeZone;

    protected $configFactory;

    /**
     * Directory List
     *
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * Io File
     *
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $ioFile;

    /**
     * XML
     *
     * @var string
     */
    protected $xml = null;

    /**
     * Root
     *
     * @var string
     */
    protected $root = null;

    const PROCESS_TYPE_INQUIRY = 'I';

    const RECURRING_ID = null;

    /**
     * Construct
     *
     * @param Context               $context       Context
     * @param StoreManagerInterface $storeManager  Store Manager Interface
     * @param DateTime              $datetime      Date Time
     * @param TimezoneInterface     $timezone      Time Zone
     * @param DirectoryList         $directoryList Directory List
     * @param IoFile                $file          File
     * @param ConfigFactory         $configFactory Config Factory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        DateTime $datetime,
        TimezoneInterface $timezone,
        DirectoryList $directoryList,
        IoFile $file,
        ConfigFactory $configFactory
    ) {
        parent::__construct($context);
        $this->storeManager  = $storeManager;
        $this->scopeConfig   = $context->getScopeConfig();
        $this->urlInterface  = $context->getUrlBuilder();
        $this->dateTime      = $datetime;
        $this->timeZone      = $timezone;
        $this->directoryList = $directoryList;
        $this->ioFile        = $file;
        $this->configFactory = $configFactory;
    }//end __construct()


    /**
     * Get merchant id
     *
     * @return string
     */
    public function getMerchantId()
    {
        return $this->getConfig('payment/ccpp/merchant_id');
    }//end getMerchantId()

    /**
     * Get secret key
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->getConfig('payment/ccpp/secret_key');
    }//end getSecretKey()

    /**
     * Get Version
     *
     * @return String
     */
    public function getVersion()
    {
        return $this->getConfig('payment/ccpp/inquiry_version');
    }

    /**
     * Get 123 Key
     *
     * @return String
     */
    public function getCcppKey()
    {
        return $this->getConfig('payment/ccpp/payment_public_key');
    }

    /**
     * Get Public Key
     *
     * @return String
     */
    public function getPublicKey()
    {
        return $this->getConfig('payment/ccpp/public_key');
    }

    /**
     * Get Private Key
     *
     * @return String
     */
    public function getPrivateKey()
    {
        return $this->getConfig('payment/ccpp/private_key');
    }

    /**
    *  Get Cron Active
    *
    * @return bool
    */
    public function getCronActive()
    {
        return (bool)$this->getConfig('payment/ccpp/enable_inquiry');
    }

    /**
     * Get Timestamp
     *
     * @return String
     */
    public function getTimestamp()
    {
        $millisec = round((microtime(true)-time())*1000);
        $date = $this->timeZone->formatDateTime(
            $this->dateTime->date(),
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::SHORT,
            null,
            null,
            'Y-MM-dd H:m:s:'
        ).$millisec;
        return $date;
    }

    /**
     * Get Hash Data
     *
     * @param string $data Data
     *
     * @return String
     */
    public function hashData($data)
    {
        $signData = hash_hmac('sha1', $data, $this->getSecretKey(), false);
        $signData =  strtoupper($signData);
        return urlencode($signData);
    }

    /**
     * Get Currency Code
     *
     * @return String
     */
    public function getCurrencyCode()
    {
        return self::CURRENCY_CODE;
    }

    /**
     * Get Country Code
     *
     * @return String
     */
    public function getCountryCode()
    {
        return self::COUNTRY_CODE;
    }

    /**
     * Get Encrypt Data
     *
     * @param string $sourceFile Source File
     * @param string $outputFile Output File
     * @param string $type       Type
     * @param bool   $log        Need To log ?
     *
     * @return String
     */
    public function encryptData(
        $sourceFile,
        $outputFile,
        $type = 'Request',
        $log = false
    ) {
        $encryptedReq = '';
        if (openssl_pkcs7_encrypt(
            $sourceFile,
            $outputFile,
            $this->getCcppKey(),
            array()
        )
        ) {
            $encryptedReq =  file_get_contents($outputFile);
            if (!$log) {
                unlink($sourceFile);
                unlink($outputFile);
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Fail to encrypt data')
            );
        }

        $encryptedReq = trim($this->removeHeader($encryptedReq));
        return $encryptedReq;
    }

    /**
     * Remove header from content
     *
     * @param string $content content
     *
     * @return String
     */
    protected function removeHeader($content)
    {
        $content=str_replace("MIME-Version: 1.0", "", $content);
        $content=str_replace(
            "Content-Disposition: attachment; filename=\"smime.p7m\"",
            "",
            $content
        );
        $content=str_replace(
            "Content-Type: application/x-pkcs7-mime; ".
                "smime-type=enveloped-data; name=\"smime.p7m\"",
            "",
            $content
        );
        $content=str_replace("Content-Transfer-Encoding: base64", "", $content);
        return $content;
    }

    /**
     * Get Output Folder
     *
     * @param string $type Type Of Folder
     *
     * @return string
     */
    public function getOutputFolder($type)
    {
        $path = $this->directoryList->getPath('media');

        if (strpos($path, '/') === false) {
            $path .= '\\ccpp\\'.$type.'\\';
        } else {
            $path .= '/ccpp/'.$type.'/';
        }

        $this->ioFile->checkAndCreateFolder($path);
        chmod($path, 0777);
        return $path;
    }

    /**
     * Get Data Source
     *
     * @param string $sourceData sourceData
     *
     * @return String
     */
    public function getDataSource($sourceData = null)
    {
        return $this->configFactory->create($sourceData);
    }

    /**
     * Get grand total
     *
     * @param Order $order Order
     *
     * @return int
     */
    public function getGrandTotal($order)
    {
        $grandTotal = 0;
        if (empty($order) === false) {
            $grandTotal = $order->getGrandTotal();
            $grandTotal = $this->convertToLeadingZeroTotalFormat($grandTotal);
        }

        return $grandTotal;
    }//end getGrandTotal()


    /**
     * Convert to leading zero format
     *
     * @param decimal $amount          Amount
     * @param int     $numberOfDitgits Number Of Ditgits
     *
     * @return string
     */
    public function convertToLeadingZeroTotalFormat(
        $amount,
        $numberOfDitgits = self::ALLOW_AMOUNT_NUMBER
    ) {
        return str_pad(($amount * 100), $numberOfDitgits, '0', STR_PAD_LEFT);
    }//end convertToLeadingZeroTotalFormat()


    /**
     * Get Form Action Url
     *
     * @return string
     */
    public function getInquiryUrl()
    {
        $testmode = $this->getConfig('payment/ccpp/sandbox_flag');
        if ($testmode === '1') {
            return $this->getConfig('payment/ccpp/inquiry_url_test');
        } else {
            return $this->getConfig('payment/ccpp/inquiry_url');
        }
    }//end getFormActionUrl()

    /**
     * Write request to file
     *
     * @param string $xml     XML
     * @param string $type    Type
     * @param int    $orderId Order Id
     *
     * @return String
     */
    public function writeRequest(
        $xml,
        $type = array('Request','Decrypt','Encrypt'),
        $orderId = null
    ) {
        $path = $this->getOutputFolder($type[0]);

        if (!is_null($orderId)) {
            $id = $orderId;
        } else {
            $id = uniqid();
        }

        $file = $path . $type[1]. $id . '.txt';
        file_put_contents($file, $xml);
        $encrypt = $path . $type[2].$id . '.txt';
        file_put_contents($encrypt, '');
        chmod($encrypt, 0777);
        chmod($file, 0777);
        return array('source' => $file, 'output' => $encrypt);
    }

    /**
    * Get XML Document
    *
    * @return XML
    */
    protected function getXml()
    {
        if (!$this->xml) {
            $this->xml = new \DOMDocument('1.0', 'ISO-8859-1');
            $this->xml->formatOutput = true;
        }
        return $this->xml;
    }

    /**
     * Get Root Of XML
     *
     * @param string $root Root Of Document
     *
     * @return XML
     */
    protected function getRoot($root)
    {
        if (!$this->root) {
            $xml = $this->getXml();
            $this->root = $xml->createElement($root);
            $xml->appendChild($this->root);
        }
        return $this->root;
    }

    /**
     * Get Simple XML
     *
     * @param array  $array Array
     * @param string $root  Root of document
     * @param string $child Child Element
     *
     * @return XML
     */
    public function getSimpleXml(array $array, $root = 'PaymentRequest', $child = '')
    {
        $xml = $this->getXml();
        if ($child) {
            $root = $child;
        } else {
            $root = $this->getRoot($root);
        }

        foreach ($array as $k => $v) {
            if (is_numeric($k) && is_array($v)) {
                foreach ($v as $sub_el => $att) {
                    $sub_el=$xml->createElement($sub_el);
                    if (is_array($att)) {
                        foreach ($att as $at1 => $av1) {
                            $at1 = $xml->createAttribute($at1);
                            $at1->value = $av1;
                            $sub_el->appendChild($at1);
                        }
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Invalid element attribute')
                        );
                    }
                    $root->appendChild($sub_el);
                }
            } elseif (!is_numeric($k)) {
                $k=$xml->createElement($k);
                $root->appendChild($k);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid array format')
                );
            }
            if (!is_array($v)) {
                if ($v) {
                    $k->appendChild($xml->createTextNode($v));
                }
            } elseif (!is_numeric($k)) {
                $this->getSimpleXml($v, '', $k);
            }
        }

        if ($child) {
            return;
        }

        $xml = $this->removeFirst($xml->saveXML());
        return $xml;
    }

    /**
     * Remove First Element Of XML
     *
     * @param string $xml XML
     *
     * @return string
     */
    protected function removeFirst($xml)
    {
        $return ='';
        $lines = explode("\n", $xml, 2);
        if (!preg_match('/^\<\?xml/', $lines[0])) {
            $return = $lines[0];
        }
        $return .= $lines[1];
        return $return;
    }

    /**
     * Decrypt Data
     *
     * @param string $responses Responses
     * @param string $type      Type
     * @param bool   $log       Need To log?
     * @param string $orderId   Order Id
     *
     * @return string
     */
    public function decryptData(
        $responses,
        $type = 'Request',
        $log = false,
        $orderId = null
    ) {

        $encryptResponse = 'MIME-Version: 1.0'.PHP_EOL.
        'Content-Disposition: attachment; filename="smime.p7m"'.PHP_EOL.
        'Content-Type: application/x-pkcs7-mime; smime-type=enveloped-data; '.
        'name="smime.p7m"'.PHP_EOL.
        'Content-Transfer-Encoding: base64'.PHP_EOL.PHP_EOL.
        $responses;

        $encryptResponse = wordwrap($encryptResponse, 64, "\n", true);

        $files = $this->writeRequest(
            $encryptResponse,
            array($type,'Encrypt','Decrypt'),
            $orderId
        );

        $encrypted = $files['source'];
        $decrypted = $files['output'];

        $passphrase = '2c2p';

        $private_key = array($this->getPrivateKey(), $passphrase);

        if (openssl_pkcs7_decrypt(
            $encrypted,
            $decrypted,
            $this->getPublicKey(),
            $private_key
        )
        ) {
            $decryptedRes =  file_get_contents($decrypted);
            $data = $this->getDataSource($decryptedRes)->getNode();
            $decryptedRes = $data->asArray();
            if (!$log) {
                unlink($encrypted);
                unlink($decrypted);
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Fail to decrypt data')
            );
            //echo 'Fail to decrypt data';
        }
        return $decryptedRes;
    }

    /**
     * Get config value
     *
     * @param string $configPath Config Path
     *
     * @return string
     */
    public function getConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }//end getConfig()


    /**
    *  Get Inquiry Request Data
    *
    * @param string $orderId Order Id
    *
    * @return array
    */
    public function getInquiryRequestData($orderId)
    {
        $req = array(
            'version'           => $this->getVersion(),
            'timeStamp'         => $this->getTimestamp(),
            'merchantID'        => $this->getMerchantId(),
            'recurringUniqueID' => self::RECURRING_ID,
            'invoiceNo'         => $orderId,
            'processType'       => self::PROCESS_TYPE_INQUIRY,
            'hashValue'         => $this->getInquiryRequestHash($orderId),
        );

        return $req;
    }

    /**
    * Get Inquiry RequestHash
    *
    * @param string $orderId Order Id
    *
    * @return string
    */
    public function getInquiryRequestHash($orderId)
    {
        $data = $this->getVersion().
                $this->getMerchantId().
                $orderId.
                self::RECURRING_ID.
                self::PROCESS_TYPE_INQUIRY;

        return $this->hashData($data);
    }


    /**
    * Process 2C2P Transaction
    *
    * @param string $orderId Order Id
    *
    * @return array
    */
    public function processTransaction($orderId)
    {
        $this->xml = null;
        $this->root = null;
        $reqData = $this->getInquiryRequestData($orderId);

        $xml = $this->getSimpleXml($reqData, 'PaymentProcessRequest');

        $files = $this->writeRequest(
            $xml,
            array('Request','Decrypt','Encrypt'),
            $orderId
        );

        $encryptData = $this->encryptData(
            $files['source'],
            $files['output'],
            false
        );

        $data = http_build_query(array('paymentRequest'=>$encryptData));
        $ch = curl_init();

        $options = array(
            CURLOPT_URL => $this->getInquiryUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
        );

        if($this->scopeConfig->getValue('payment/ccpp/proxy_enable')) {
            $proxyUrl = $this->scopeConfig->getValue('curl_proxy/curl_proxy_setup/proxy_url');
            $options[CURLOPT_PROXY] = $proxyUrl;
            $options[CURLOPT_SSL_VERIFYPEER] = false;

        }

        curl_setopt_array($ch, $options);
        $responses = curl_exec($ch);
        $responseCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (!curl_errno($ch)) {
            $responsesData = $this->decryptData(
                $responses,
                'Responses',
                true,
                $orderId
            );
            return $responsesData;

            // @codingStandardsIgnoreStart
            //array ( 'version' => '2.1', 'timeStamp' => '2017-10-11 11:34:0:20', 'respCode' => '00', 'respReason' => 'Inquiry Successful', 'pan' => '490734XXXXXX0703', 'amt' => '000000156000', 'invoiceNo' => '1000001707', 'tranRef' => '1011110009898', 'approvalCode' => '701146', 'eci' => '05', 'dateTime' => '20171011112219', 'status' => 'A', 'failReason' => 'Inquiry Successful', 'hashValue' => '302AD8B517B70FF9DD1E67C05FAF968C7EE0558E', )


            //array ( 'version' => '2.1', 'timeStamp' => '2017-10-11 11:37:4:289', 'respCode' => '00', 'respReason' => 'Inquiry Successful', 'pan' => '441770XXXXXX8393', 'amt' => '000000156000', 'invoiceNo' => '1000001645', 'tranRef' => '1010110020006', 'approvalCode' => '007650', 'eci' => '05', 'dateTime' => '20171010160059', 'status' => 'S', 'failReason' => 'Inquiry Successful', 'hashValue' => '3C294A9577F1F8099EE670152A45443DDC7D7D5A', )
            // @codingStandardsIgnoreEnd
        }
        curl_close($ch);

        return false;
    }

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    public function getInvoiceEmailConfig()
    {
        return $this->getConfigValue(self::XML_PATH_CONFIG, $this->storeManager->getStore()->getId());
    }
}//end class
