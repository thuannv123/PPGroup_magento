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

namespace Mageplaza\OrderAttributes\Controller\Adminhtml\Step;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\OrderAttributes\Controller\Adminhtml\Step;
use Mageplaza\OrderAttributes\Model\Config\Source\PositionStep;

/**
 * Class Validate
 * @package Mageplaza\OrderAttributes\Controller\Adminhtml\Step
 */
class Validate extends Step
{
    const VALIDATION_RULE_PATTERN = '/^[a-z][a-z_0-9]{0,59}$/';

    /**
     * @return void
     * @throws LocalizedException
     */
    public function execute()
    {
        $response = new DataObject();
        $response->setError(false);
        $request = $this->getRequest();
        $code    = trim($request->getParam('code') ?? '');
        $stepId  = $request->getParam('id');

        $stepObj = $this->_initStep();
        if ($stepId) {
            $stepObj->load($stepId);
            if (!$stepObj->getId()) {
                $this->showMessage($response, __('The Step with the "%1" ID doesn\'t exist.', $stepId));
            }
        } else {
            $this->validateCode($stepObj, $code, $response);
        }
        $this->validateSortOrder($stepObj, $request, $response);
        $name = trim($request->getParam('name') ?? '');
        if (!$name) {
            $this->showMessage($response, __('Name is required.', $stepId));
        }

        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * @param $response
     * @param $message
     */
    public function showMessage($response, $message)
    {
        $this->messageManager->addErrorMessage($message);
        $this->_view->getLayout()->initMessages();
        $response->setData([
            'error'        => true,
            'html_message' => $this->_view->getLayout()->getMessagesBlock()->getGroupedHtml()
        ]);
    }

    /**
     * @param string $code
     * @param \Mageplaza\OrderAttributes\Model\Step $stepObj
     * @param $response
     *
     * @return bool
     * @throws InputException
     * @throws LocalizedException
     */
    public function validateCode($stepObj, $code, $response)
    {
        if (!$code) {
            throw new InputException(__('Code is required.'));
        }
        if ($code === 'payment' || $code === 'shipping') {
            $message = __('Code "%1" need to Need different from "shipping" and "payment"', $code);
            $this->showMessage($response, $message);
        }

        if (!preg_match(self::VALIDATION_RULE_PATTERN, $code)) {
            $message = __(
                'Code "%1" is invalid. Please use only letters (a-z), numbers (0-9)' .
                'or underscore(_) in this field, first character should be a letter.',
                $code
            );
            $this->showMessage($response, $message);
        }

        if ($stepObj->loadByCode($code)) {
            $this->showMessage($response, __('An Step with this Code "%1" already exists.', $code));
        }

        return true;
    }

    /**
     * @param \Mageplaza\OrderAttributes\Model\Step $stepObj
     * @param RequestInterface $request
     * @param $response
     *
     * @return bool
     * @throws InputException
     * @throws LocalizedException
     */
    public function validateSortOrder($stepObj, $request, $response)
    {
        $position  = (int) $request->getParam('position');
        $sortOrder = (int) $request->getParam('sort_order');

        if ($position === PositionStep::BEFORE_SHIPPING && !($sortOrder < 10 && $sortOrder >= 0)) {
            $this->showMessage($response, __('To show Before Shipping, the Sort Order need to greater than 0 and less than 10.'));
        }
        if ($position === PositionStep::AFTER_SHIPPING && !($sortOrder < 20 && $sortOrder > 10)) {
            $this->showMessage($response, __('To show After Shipping, the Sort Order need to greater than 10 and less than 20.'));
        }
        if ((int) $stepObj->getSortOrder() === $sortOrder) {
            return true;
        }
        if ($stepObj->loadBySortOrder($sortOrder)) {
            $this->showMessage($response, __('An Step with this Sort Order "%1" already exists.', $sortOrder));
        }

        return true;
    }
}
