<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Popup
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Popup\Model;

use Bss\Popup\Model\ResourceModel\Popup;
use Magento\Framework\App\Helper\Context;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var string $configSectionId
     */
    protected $_configSectionId = 'popup';

    /**
     * @var Popup
     */
    protected $popupResourceModel;

    /**
     * @param Context $context
     * @param Popup $popupResourceModel
     */
    public function __construct(
        Context $context,
        Popup $popupResourceModel
    ) {
        $this->popupResourceModel = $popupResourceModel;
        parent::__construct($context);
    }

    /**
     * Return the config flag
     *
     * @param string $path
     * @param string $store
     * @param string $scope
     * @return bool
     */
    public function hasConfigFlag($path, $store = null, $scope = null)
    {
        if ($scope === null) {
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }
        return $this->scopeConfig->isSetFlag($path, $scope, $store);
    }

    /**
     * Check status of the config
     *
     * @return bool
     */
    public function isEnable()
    {
        return $this->hasConfigFlag($this->_configSectionId . '/general/enable');
    }
}
