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

namespace Mageplaza\OrderAttributes\Plugin\Quote;

use Closure;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsExtensionFactory;
use Magento\Quote\Api\Data\TotalsExtensionInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Mageplaza\OrderAttributes\Helper\Data;

/**
 * Class CartTotalRepository
 * @package Mageplaza\OrderAttributes\Plugin\Quote
 */
class CartTotalRepository
{
    /**
     * @var TotalsExtensionFactory
     */
    protected $totalExtensionFactory;
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    private $quoteRepository;
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * CartTotalRepository constructor.
     *
     * @param TotalsExtensionFactory $totalExtensionFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param Data $helperData
     */
    public function __construct(
        TotalsExtensionFactory $totalExtensionFactory,
        CartRepositoryInterface $quoteRepository,
        Data $helperData
    ) {
        $this->totalExtensionFactory = $totalExtensionFactory;
        $this->quoteRepository       = $quoteRepository;
        $this->helperData            = $helperData;
    }

    /**
     * @param CartTotalRepositoryInterface $subject
     * @param Closure $proceed
     * @param $cartId
     *
     * @return TotalsInterface
     * @throws NoSuchEntityException
     */
    public function aroundGet(CartTotalRepositoryInterface $subject, Closure $proceed, $cartId)
    {
        /** @var TotalsInterface $quoteTotals */
        $quoteTotals = $proceed($cartId);

        if (!$this->helperData->isEnabled()) {
            return $quoteTotals;
        }

        /** @var TotalsExtensionInterface $totalsExtension */
        $totalsExtension = $quoteTotals->getExtensionAttributes() ?: $this->totalExtensionFactory->create();
        $quote           = $this->quoteRepository->getActive($cartId);

        $steps = $this->helperData->registry->registry('mp_order_attributes_steps');
        if (!$steps) {
            $address = $quote->getShippingAddress();
            $steps   = $this->helperData->getStepCodesFiltered(clone $address);
        }
        $totalsExtension->setMpOrderattributesSteps($steps);

        $quoteTotals->setExtensionAttributes($totalsExtension);

        return $quoteTotals;
    }
}
