<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amastyfixed\GDPR\Controller\Adminhtml\Subscriber;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Model\Consent;
use Amasty\Gdpr\Api\WithConsentRepositoryInterface;
use Amasty\Gdpr\Model\Source\ConsentLinkType;
use Amasty\Gdpr\Model\Consent\ResourceModel\CollectionFactory;
use Amasty\Gdpr\Model\WithConsentFactory;
use Amastyfixed\GDPR\Helper\Data as GdprHelper;
use Magento\Newsletter\Controller\Adminhtml\Subscriber;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;


class MassUnsubscribe extends Subscriber
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;
    private $helper;
    private $collectionFactory;
    private $withConsentRepository;
    private $consentFactory;
    const ADMIN_FORM  = 'admin';

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        SubscriberFactory $subscriberFactory = null,
        GdprHelper $helper,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        PolicyRepositoryInterface $policyRepository,
        WithConsentRepositoryInterface $withConsentRepository,
        WithConsentFactory $consentFactory
    ) {
        $this->withConsentRepository = $withConsentRepository;
        $this->consentFactory = $consentFactory;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->policyRepository = $policyRepository;
        $this->subscriberFactory = $subscriberFactory ?: ObjectManager::getInstance()->get(SubscriberFactory::class);
        parent::__construct($context, $fileFactory);
    }

    /**
     * Unsubscribe one or more subscribers action
     *
     * @return void
     */
    public function execute()
    {
        $subscribersIds = $this->getRequest()->getParam('subscriber');
        if (!is_array($subscribersIds)) {
            $this->messageManager->addError(__('Please select one or more subscribers.'));
        } else {
            try {
                $policy = $this->policyRepository->getCurrentPolicy();
                $consentsCollection = $this->collectionFactory
                    ->create()
                    ->addStoreData(1)
                    ->addFieldToFilter(Consent\Consent::CONSENT_CODE, ['in' => $this->helper->getDeclineCheckboxCode()])
                    ->addFieldToFilter(Consent\ConsentStore\ConsentStore::LOG_THE_CONSENT, true)
                    ->addFieldToFilter(Consent\ConsentStore\ConsentStore::IS_ENABLED, true);
                // print_r($consentsCollection->getData()); exit;
                foreach ($subscribersIds as $subscriberId) {
                    $subscriber = $this->subscriberFactory->create()->load(
                        $subscriberId
                    );
                    $subscriber->setAction('Decline');
                    $subscriber->save();
                    $subscriber->unsubscribe();

                    foreach ($consentsCollection as $consent) {
                        $withConsent = $this->consentFactory->create();
                        $privacyPolicyVersionValue = $consent->getPrivacyLinkType() === ConsentLinkType::PRIVACY_POLICY ?
                            $policy->getPolicyVersion() : self::CMS_PAGE;
                        $withConsent->setPolicyVersion($privacyPolicyVersionValue);
                        $withConsent->setGotFrom(self::ADMIN_FORM);
                        $withConsent->setWebsiteId($this->storeManager->getWebsite()->getId());
                        //$withConsent->setIp($this->visitor->getRemoteIp());
                        $withConsent->setCustomerId($subscriber->getCustomerId());
                        $withConsent->setCustomerEmail($subscriber->getEmail());
                        $withConsent->setAction(0);
                        $withConsent->setConsentCode($consent->getConsentCode());
                        $this->withConsentRepository->save($withConsent);
                    }
                }
                $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', count($subscribersIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
