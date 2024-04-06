<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Email Unsubscribe for Magento 2 (System)
 */

namespace Amasty\EmailUnsubscribe\Model\Observer;

use Amasty\EmailUnsubscribe\Model\ResourceModel\GetEmailsByType;
use Amasty\EmailUnsubscribe\Model\ResourceModel\Unsubscribe;
use Amasty\EmailUnsubscribe\Model\ResourceModel\UnsubscribeType;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UnsubscribeEmails implements ObserverInterface
{
    /**
     * @var Unsubscribe
     */
    private $unsubscribe;

    /**
     * @var GetEmailsByType
     */
    private $getEmailsByType;

    public function __construct(
        Unsubscribe $unsubscribe,
        GetEmailsByType $getEmailsByType
    ) {
        $this->unsubscribe = $unsubscribe;
        $this->getEmailsByType = $getEmailsByType;
    }

    public function execute(Observer $observer): void
    {
        $transportObject = $observer->getData('transport_object');
        $emails = $transportObject->getData('emails') ?: [];
        $entityId = (int) $transportObject->getData('entity_id');
        $type = $transportObject->getData(UnsubscribeType::TYPE) ?: '';
        $unsubscribedEmails = $this->getEmailsByType->execute($type, [$entityId, 0]);
        $emails = array_diff($emails, $unsubscribedEmails);
        $transportObject->setData('emails', $emails);
    }
}
