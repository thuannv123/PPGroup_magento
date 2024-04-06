<?php

namespace Amastyfixed\GDPR\Observer;

use Amasty\Gdpr\Api\WithConsentRepositoryInterface;
use Magento\Framework\Event\Observer;
use Amasty\Gdpr\Model\WithConsentFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Amasty\Gdpr\Model\ResourceModel\WithConsent\CollectionFactory;

class AnonymizerLog implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $customerRepositoryInterface;
    private $consentFactory;
    private $withConsentCollectionFactory;
    private $withConsentRepository;

    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        WithConsentRepositoryInterface $withConsentRepository,
        CollectionFactory $collectionFactory,
        WithConsentFactory $consentFactory
    ) {
        $this->consentFactory = $consentFactory;
        $this->withConsentCollectionFactory = $collectionFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->withConsentRepository = $withConsentRepository;
    }

    /**
     * @param Observer $observer
     *
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $customer_id = $observer->getData('customerId');
        $email = $this->customerRepositoryInterface->getById($customer_id)->getEmail();
        $withConsentCollection = $this->withConsentCollectionFactory
            ->create()
            ->addFieldToFilter('customer_id', $customer_id);
        /** @var Consent\Consent $consent */
        foreach ($withConsentCollection->getItems() as $withConsents) {
            $withConsent = $this->consentFactory->create();
            $consent  = $withConsent->load($withConsents->getId());
            $consent->setCustomerEmail($email);
            $this->withConsentRepository->save($consent);
        }
    }
}