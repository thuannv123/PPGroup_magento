<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace PPGroup\ZipcodeSuggestion\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;

/**
 * @inheritdoc
 */
class ShippingAddressValidationRule extends \Magento\Quote\Model\ValidationRules\ShippingAddressValidationRule
{
    /**
     * @var string
     */
    private $generalMessage;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param string $generalMessage
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        string $generalMessage = ''
    ) {
        parent::__construct($validationResultFactory,$generalMessage);
        $this->validationResultFactory = $validationResultFactory;
        $this->generalMessage = $generalMessage;
    }

    /**
     * @inheritdoc
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];

        if (!$quote->isVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setStoreId($quote->getStoreId());
            $validationResult = $shippingAddress->validate();
            if ($validationResult !== true) {
            }
            if (is_array($validationResult)) {
                $validationErrors = array_merge($validationErrors, $validationResult);
            }
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
