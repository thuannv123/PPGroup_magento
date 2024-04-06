<?php

namespace WeltPixelFixed\Backend\Model;

class License extends \WeltPixel\Backend\Model\License
{

    /**
     * @return array
     */
    protected function getAvlbMds()
    {
        if ($this->_attempt < 3 && empty($this->modulesList)) {

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $curl = curl_init(\WeltPixel\Backend\Block\Adminhtml\ModulesVersion::MODULE_VERSIONS);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            if($objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('weltpixel_backend_developer/proxy/enabled')) {
                $proxyUrl = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('curl_proxy/curl_proxy_setup/proxy_url');
                curl_setopt($curl, CURLOPT_PROXY, $proxyUrl);
            }
            try {
                $response = curl_exec($curl);
                $modulesList = json_decode($response, true);
                $this->modulesList = array_keys($modulesList['modules']);

                foreach ($this->modulesList as $module) {
                    if (isset($modulesList['modules'][$module]['name'])) {
                        $this->modulesUserFriendlyNames[$module] = $modulesList['modules'][$module]['name'];
                    }
                }

            } catch (\Exception $ex) {
                $this->_attempt+=1;
                $this->modulesList = [];
                $this->modulesUserFriendlyNames = [];
            }

        }

        return $this->modulesList;
    }

    /**
     * @param bool $all
     * @param array $modules
     */
    public function updMdsInf($all = true, $modules = []) {
        if ($all) {
            $modules = $this->getAllWpMds();
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $baseUrl = $this->urlInterface->getBaseUrl();
        $domainInfo = parse_url($baseUrl);
        $domain = $domainInfo['host'];
        $magentoVersion = strtolower($this->productMetadata->getEdition());

        $data = array(
                "\x76\x65\x72\x73\x69\x6f\x6e" => $magentoVersion,
                "\x64\x6f\x6d\x61\x69\x6e" => $domain,
                "\x6d\x6f\x64\x75\x6c\x65\x73" => $modules
        );

        $data_string = json_encode($data);

        try {
            $ch = curl_init(self::LICENSE_ENDPOINT);
            if($objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('weltpixel_backend_developer/proxy/enabled')) {
                $proxyUrl = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('curl_proxy/curl_proxy_setup/proxy_url');
                curl_setopt($ch, CURLOPT_PROXY, $proxyUrl);
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Content-Type: application/json',
                            'Content-Length: ' . strlen($data_string))
            );

            $result = curl_exec($ch);
            $this->_prsLcInf($result);
        } catch (\Exception $ex) {
            $this->_uLcInRs(0);
        }
    }


}
