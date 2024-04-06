<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PPGroup\Newsletter\Plugin\Model;

use Magento\Newsletter\Model\SubscriptionManager as MagentoSubscriptionManager;

use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\MailException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\CustomerSubscriberCache;
use Magento\Framework\App\ResourceConnection;

/**
 * Class to update newsletter subscription status
 */
class SubscriptionManager
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerSubscriberCache
     */
    private $customerSubscriberCache;

    /***
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param SubscriberFactory $subscriberFactory
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerSubscriberCache|null $customerSubscriberCache
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        SubscriberFactory $subscriberFactory,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        AccountManagementInterface $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        ResourceConnection $resourceConnection,
        CustomerSubscriberCache $customerSubscriberCache = null
    ) {
        $this->subscriberFactory = $subscriberFactory;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->resourceConnection = $resourceConnection;
        $this->customerSubscriberCache = $customerSubscriberCache
            ?? ObjectManager::getInstance()->get(CustomerSubscriberCache::class);
    }

    /**
     * @param MagentoSubscriptionManager $subject
     * @param callable $proceed
     * @param string $email
     * @param int $storeId
     */
    public function aroundSubscribe(MagentoSubscriptionManager $subject, callable $proceed, string $email, int $storeId)
    {
        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        $subscriber = $this->subscriberFactory->create()->loadBySubscriberEmail($email, $websiteId);
        $currentStatus = (int)$subscriber->getStatus();
        if ($currentStatus === Subscriber::STATUS_SUBSCRIBED) {
            return $subscriber;
        }

        $status = $this->isConfirmNeed($storeId) ? Subscriber::STATUS_NOT_ACTIVE : Subscriber::STATUS_SUBSCRIBED;
        if (!$subscriber->getId()) {
            $subscriber->setSubscriberConfirmCode($subscriber->randomSequence());
            $subscriber->setSubscriberEmail($email);
        }
        $subscriber->setStatus($status)
            ->setStoreId($storeId);

        $subscriberData = $subscriber->getData();

        $connection = $this->resourceConnection->getConnection();

        $now = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

        if (!$this->getCustomerId($email, $websiteId)) {
            if (!$subscriber->getId()) {
                $query = "INSERT INTO `newsletter_subscriber` (`subscriber_email`, `subscriber_confirm_code`, `subscriber_status`, `store_id`, `change_status_at`, `customer_id`)
                 VALUES ('{$subscriberData['subscriber_email']}',
                         '{$subscriberData['subscriber_confirm_code']}',
                         '{$subscriberData['subscriber_status']}',
                         '{$subscriberData["store_id"]}',
                         '{$now}',
                         '0')";

            $connection->query($query);

            $subscriber = $this->subscriberFactory->create()->loadBySubscriberEmail($email, $websiteId);
            }
        }

        $this->sendEmailAfterChangeStatus($subscriber);

        return $subscriber;
    }

    /**
     * Sends out email to customer after change subscription status
     *
     * @param Subscriber $subscriber
     * @return void
     */
    private function sendEmailAfterChangeStatus(Subscriber $subscriber): void
    {
        $status = (int)$subscriber->getStatus();
        if ($status === Subscriber::STATUS_UNCONFIRMED) {
            return;
        }

        try {
            switch ($status) {
                case Subscriber::STATUS_UNSUBSCRIBED:
                    $subscriber->sendUnsubscriptionEmail();
                    break;
                case Subscriber::STATUS_SUBSCRIBED:
                    $subscriber->sendConfirmationSuccessEmail();
                    break;
                case Subscriber::STATUS_NOT_ACTIVE:
                    $subscriber->sendConfirmationRequestEmail();
                    break;
            }
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        }
    }

    /**
     * Is need to confirm subscription
     *
     * @param int $storeId
     * @return bool
     */
    private function isConfirmNeed(int $storeId): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            Subscriber::XML_PATH_CONFIRMATION_FLAG,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if customer with provided email exists and return its id
     *
     * @param string $email
     * @param int $websiteId
     * @return int|null
     */
    private function getCustomerId(string $email, int $websiteId): ?int
    {
        try {
            $customer = $this->customerRepository->get($email, $websiteId);
            return (int)$customer->getId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }
}
