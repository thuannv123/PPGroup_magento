<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Controller\Cookie;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Amasty\GdprCookie\Model\CookieConsentLogger;
use Amasty\GdprCookie\Model\SaveCookiesConsent;
use Amasty\GdprCookie\Model\CookieManager;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class SaveGroups implements \Magento\Framework\App\ActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var SaveCookiesConsent
     */
    private $saveCookiesConsent;

    public function __construct(
        RequestInterface $request,
        Session $session,
        StoreManagerInterface $storeManager,
        ?CookieManager $cookieManager, // @deprecated. Backward compatibility
        ManagerInterface $messageManager,
        ?CookieConsentLogger $consentLogger, // @deprecated. Backward compatibility
        ?CookieManagementInterface $cookieManagement, // @deprecated. Backward compatibility
        ResultFactory $resultFactory,
        SaveCookiesConsent $saveCookiesConsent = null
    ) {
        $this->request = $request;
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        // OM for backward compatibility
        $this->saveCookiesConsent = $saveCookiesConsent ?? ObjectManager::getInstance()->get(SaveCookiesConsent::class);
    }

    public function execute()
    {
        /** @var Json $response */
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $storeId = (int)$this->storeManager->getStore()->getId();
        $allowedCookieGroupIds = (array)$this->request->getParam('groups');
        $customerId = (int)$this->session->getCustomerId();
        $result = $this->saveCookiesConsent->execute($allowedCookieGroupIds, $storeId, $customerId);
        $this->messageManager->addSuccessMessage(__($result['message']));

        return $response->setData($result['success']);
    }
}
