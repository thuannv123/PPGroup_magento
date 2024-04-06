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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Popup\Helper;

use Bss\Popup\Model\HandleLayout;
use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Bss\Popup\Model\ResourceModel\Popup
     */
    protected $popupResourceModel;

    /**
     * Scope Config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Popup Cookie
     *
     * @var \Bss\Popup\Model\PopupCookie
     */
    protected $popupCookie;

    /**
     * Session Manager
     *
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $sessionManager;

    /**
     * Customer Session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Bss\Popup\Model\Form\FormKey
     */
    private $formKey;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $_cookieManager;

    /**
     * @var HandleLayout $handleLayout
     */
    protected $handleLayout;

    /**
     * Data constructor.
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Bss\Popup\Model\PopupCookie $popupCookie
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Session\SessionManagerFactory $sessionManager
     * @param \Bss\Popup\Model\ResourceModel\Popup $popupResourceModel
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Bss\Popup\Model\Form\FormKey $formKey
     * @param HandleLayout $handleLayout
     */
    public function __construct(
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Bss\Popup\Model\PopupCookie $popupCookie,
        \Magento\Framework\Session\SessionManagerFactory $sessionManager,
        \Bss\Popup\Model\ResourceModel\Popup $popupResourceModel,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Bss\Popup\Model\Form\FormKey $formKey,
        HandleLayout $handleLayout
    ) {
        $this->_cookieManager = $cookieManager;
        $this->popupCookie = $popupCookie;
        $this->sessionManager = $sessionManager;
        $this->customerSession = $customerSession;
        $this->popupResourceModel = $popupResourceModel;
        $this->productMetadata = $productMetadata;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->formKey = $formKey;
        $this->handleLayout = $handleLayout;
        parent::__construct($context);
    }

    /**
     * Get data Popup
     *
     * @param string $handleList
     * @param int $storeId
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @deprecated since version 1.1.8
     * @codingStandardsIgnoreStart
     */
    public function getPopup($handleList, $storeId)
    {
        $customerGroupId = $this->getCustomerGroupId();
        return $this->handleLayout->getPopupId($handleList, $storeId, $customerGroupId);
    }
    // @codingStandardsIgnoreEnd

    /**
     * @param $stores
     * @param $storeId
     * @return bool
     * @deprecated since version 1.1.8
     */
    public function checkStore($stores, $storeId)
    {
        return $this->handleLayout->checkStore($stores, $storeId);
    }

    /**
     * Check customer group
     *
     * @param mixed $customerGroup
     * @param int $customerGroupId
     * @return void
     * @deprecated since version 1.1.8
     */
    public function checkCustomerGroup($customerGroup, $customerGroupId)
    {
        $this->handleLayout->checkCustomerGroup($customerGroup, $customerGroupId);
    }

    /**
     * Is popup expired
     *
     * @param array $row
     * @return void
     * @deprecated since version 1.1.8
     */
    public function isPopupExpired($row)
    {
        $this->handleLayout->isPopupExpired($row);
    }

    /**
     * Check exclude
     *
     * @param string $handleList
     * @param mixed $excludeHandle
     * @return void
     * @deprecated since version 1.1.8
     */
    public function checkExclude($handleList, $excludeHandle)
    {
        $this->handleLayout->checkExclude($handleList, $excludeHandle);
    }

    /**
     * @return mixed
     */
    public function getSessionPageViewedByCustomer()
    {
        if ($this->sessionManager->create()->getPagesViewedByCustomer()) {
            $this->sessionManager->create()->setPagesViewedByCustomer($this->sessionManager->create()->getPagesViewedByCustomer() + 1);
        } else {
            $this->sessionManager->create()->setPagesViewedByCustomer(1);
        }

        return $this->sessionManager->create()->getPagesViewedByCustomer();
    }

    /**
     * @param $id
     */
    public function addPopupToSessionDisplayedPopup($id)
    {
        $displayedPopup = (!empty($this->sessionManager->create()->getDisplayedPopups())) ?
            $this->sessionManager->create()->getDisplayedPopups() : [0];

        if (!in_array($id, $displayedPopup)) {
            $displayedPopup[] = $id;
        }

        $this->sessionManager->create()->setDisplayedPopups($displayedPopup);
    }

    /**
     * @param $id
     * @return bool|int
     */
    public function popupNotInSession($id)
    {
        $displayed = true;
        $showed = $this->_cookieManager->getCookie('showed');
        $listPopupDisplayed = (!empty($this->sessionManager->create()->getDisplayedPopups())) ?
            $this->sessionManager->create()->getDisplayedPopups() : [0];
        if (in_array($id, $listPopupDisplayed) && $showed != null) {
            $displayed = 0;
        }
        return $displayed;
    }

    /**
     * @param $id
     * @param $duration
     * @throws \Exception
     */
    public function addPopupToCookie($id, $duration)
    {
        $cookieName = "popupCookie" . $id;
        if (empty($this->popupCookie->get($cookieName))) {
            $this->popupCookie->set($cookieName, "popup{$id}", $duration);
        }
    }

    /**
     * @param $id
     * @return string
     */
    public function getPopupCookie($id)
    {
        $cookieName = "popupCookie" . $id;
        return $this->popupCookie->get($cookieName);
    }

    /**
     * @param $popup
     * @return bool|int
     */
    public function popupIsAllowedDisplay($popup)
    {
        switch ($popup['frequently']) {
            case 1:
                return true;
            case 2:
                return $this->popupNotInSession($popup['popup_id']);
            case 3:
                if (!empty($this->getPopupCookie($popup['popup_id']))) {
                    return 0;
                }
                return true;
            default:
                return 0;
        }
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        if ($this->customerSession->create()->isLoggedIn()) {
            $groupId = $this->customerSession->create()->getCustomerGroupId();
            return $groupId;
        }
        return 0;
    }

    /**
     * @return bool
     */
    public function isAjaxCartBssEnable()
    {
        return $this->scopeConfig->isSetFlag(
            'ajaxcart/general/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getAddToCartSelector()
    {
        return $this->scopeConfig->getValue(
            'ajaxcart/general/selector',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function isM24Version()
    {
        return version_compare($this->productMetadata->getVersion(), '2.4.0', '>=');
    }

    /**
     * Get url preview
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUrlPreview()
    {
        $popupId = $this->request->getParam('popup_id', null);
        $formKey = $this->formKey->getFormKey();
        $base = $this->_getUrl('/');
        $addParamUrlPreview = $this->addParamUrlPreview();
        if (!empty($popupId)) {
            return trim($base, '/') . $addParamUrlPreview . '&preview=1&id=' . $popupId . '&cid=' . $formKey;
        }
        return trim($base, '/') . $addParamUrlPreview . '&preview=1&id=' . -1 . '&cid=' . $formKey;
    }

    /**
     * Add param when get url preview
     *
     * @return string
     */
    public function addParamUrlPreview()
    {
        if (version_compare($this->productMetadata->getVersion(), '2.3.5', '>=')) {
            return "?";
        }
        return "";
    }
}
