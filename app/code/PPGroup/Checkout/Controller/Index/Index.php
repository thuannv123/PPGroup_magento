<?php

namespace PPGroup\Checkout\Controller\Index;

use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Store\Model\StoreManagerInterface;

class Index extends \Magento\Framework\App\Action\Action implements ViewInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Validator
     */
    protected $_formKeyValidator;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @var LocalizedToNormalized
     */
    protected $localizedToNormalized;

    /**
     * Index constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param LocalizedToNormalized $localizedToNormalized
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        CustomerCart $cart,
        LocalizedToNormalized $localizedToNormalized
    ) {
        $this->_formKeyValidator = $formKeyValidator;
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->cart = $cart;
        $this->localizedToNormalized = $localizedToNormalized;
        parent::__construct($context);
    }

    public function execute($coreRoute = null)
    {
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                // $filter = new \Zend_Filter_LocalizedToNormalized(
                //     ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                // );
                $filter = $this->localizedToNormalized->setOptions(
                    ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                );
                $itemId = null;
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $itemId = $index;
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                if (!$this->cart->getCustomerSession()->getCustomerId() && $this->cart->getQuote()->getCustomerId()) {
                    $this->cart->getQuote()->setCustomerId(null);
                }
                $cartData = $this->cart->suggestItemsQty($cartData);
                $this->cart->updateItems($cartData)->save();

                $priceHelper = $this->_objectManager->create('Magento\Framework\Pricing\Helper\Data');
                $items = $this->cart->getQuote()->getAllItems();
                $disccount = null;
                $itemTotalPrice = null;
                foreach ($items as $item) {
                    if ($itemId == $item->getId()) {
                        $itemTotalPrice = $item->getQty() * $item->getPrice();
                    }
                    $disccount += $item->getTotalDiscountAmount();
                }
                $subtotal = $priceHelper->currency($this->cart->getQuote()->getSubtotal(), true, false);
                $grandtotal = $priceHelper->currency($this->cart->getQuote()->getGrandTotal(), true, false);

                $result = [
                    'subtotal' => $subtotal,
                    'grandtotal' => $grandtotal
                ];
                if ($itemTotalPrice) {
                    $itemtotalprice = $priceHelper->currency($itemTotalPrice, true, false);
                    $result['itemtotalprice'] = $itemtotalprice;
                }
                if ($disccount) {
                    $disccount = $priceHelper->currency($disccount, true, false);
                    $result['disccount'] = $disccount;
                }
                $this->getResponse()->setBody(json_encode($result));
            }
        } catch (LocalizedException $exception) {
            /* Handle Product Qty not enough*/
            $error_msg = $exception->getMessage();
            $this->messageManager->addErrorMessage($error_msg);
            $this->getResponse()
                ->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST)
                ->setBody(json_encode(['error_msg' => $error_msg]));
        }
    }
}
