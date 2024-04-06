<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_OrderAttributes
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\OrderAttributes\Model\Plugin\Checkout;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Checkout\Model\Session;
use Mageplaza\OrderAttributes\Helper\Data;

/**
 * Class GuestPaymentInformationManagement
 * @package Mageplaza\OrderAttributes\Model\Plugin\Checkout
 */
class GuestPaymentInformationManagement
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * ShippingInformationManagement constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param Data $data
     * @param Session $checkoutSession
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        Data $data,
        Session $checkoutSession
    ) {
        $this->cartRepository     = $cartRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->data               = $data;
        $this->checkoutSession    = $checkoutSession;
    }

    /**
     * @param \Magento\Checkout\Model\GuestPaymentInformationManagement $subject
     * @param string $cartId
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function beforeSavePaymentInformation(
        \Magento\Checkout\Model\GuestPaymentInformationManagement $subject,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quote       = $this->cartRepository->get($quoteIdMask->getQuoteId());

        $extensionAttributes = $paymentMethod->getExtensionAttributes();
        $isEnable            = $this->data->isEnabled($quote->getStoreId());

        if ($isEnable && $extensionAttributes && $extensionAttributes->getMpOrderAttributes()) {
            $attributes = $extensionAttributes->getMpOrderAttributes();
            $quote->setMpOrderAttributes($attributes);
        }

        $quoteSubmitAttributes = $quote->getMpOrderAttributes();

        if ($quoteSubmitAttributes) {
            $this->checkoutSession->setMpOrderAttributes($quoteSubmitAttributes);
        }

        return [$cartId, $email, $paymentMethod, $billingAddress];
    }
}
