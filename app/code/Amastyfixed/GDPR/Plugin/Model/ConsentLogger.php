<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amastyfixed\GDPR\Plugin\Model;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Api\WithConsentRepositoryInterface;
use Amasty\Gdpr\Model\WithConsentFactory;
use Amasty\Gdpr\Model\ActionLogger;
use Amasty\Gdpr\Model\Visitor;
use Amasty\Gdpr\Model\ResourceModel\WithConsent as WithConsentResource;
use Amasty\Gdpr\Model\Source\ConsentLinkType;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Amastyfixed\GDPR\Helper\Data as GdprHelper;
class ConsentLogger
{
    const FROM_REGISTRATION = 'registration';

    const FROM_CHECKOUT = 'checkout';

    const FROM_SUBSCRIPTION = 'subscription';

    const FROM_CONTACTUS = 'contactus';

    const FROM_EMAIL = 'email';

    const CMS_PAGE = 'CMS Page';

    /**
     * @var WithConsentRepositoryInterface
     */
    private $withConsentRepository;

    /**
     * @var WithConsentFactory
     */
    private $consentFactory;

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var WithConsentResource
     */
    private $withConsent;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Gdpr\Model\Visitor
     */
    private $visitor;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var GdprHelper
     */
    private $helper;

    private $customerRepositoryInterface;

    public function __construct(
        WithConsentRepositoryInterface $withConsentRepository,
        WithConsentFactory $consentFactory,
        PolicyRepositoryInterface $policyRepository,
        ActionLogger $logger,
        WithConsentResource $withConsent,
        StoreManagerInterface $storeManager,
        Visitor $visitor,
        Session $session,
        CustomerRepositoryInterface $customerRepositoryInterface,
        SubscriberFactory $subscriberFactory,
        GdprHelper $helper
    )
    {
        $this->withConsentRepository = $withConsentRepository;
        $this->consentFactory = $consentFactory;
        $this->policyRepository = $policyRepository;
        $this->logger = $logger;
        $this->withConsent = $withConsent;
        $this->storeManager = $storeManager;
        $this->visitor = $visitor;
        $this->session = $session;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->subscriberFactory = $subscriberFactory;
        $this->helper = $helper;
    }

    /**
     * @param string|int $customerId
     * @param string $from
     * @param Consent\Consent $consentModel
     *
     * @return void
     * @throws LocalizedException
     */
    public function aroundLog(\Amasty\Gdpr\Model\ConsentLogger $subject, callable $proceed, $customerId, $from, $consentModel = null)
    {
        if ($from == 'registration' && $customerId == 0) {
            return;
        }

        if ($customerId) {
            $customer = $this->customerRepositoryInterface->getById($customerId);
            $email = $customer->getEmail();
        } else {
            $email = ($this->session->getCustomerEmail()) ? $this->session->getCustomerEmail() : '';
        }

        $checkSubscriber = $this->subscriberFactory->create()->loadByEmail($email);
        $checkboxcodes = explode(',', $this->helper->getCheckBoxCode());

        if (in_array($consentModel->getConsentCode(), $checkboxcodes)) {
            if ($consentModel->isConsentAccepted()) {
                $checkSubscriber->subscribe($email);

                if ($from == 'registration') {
                    $checkSubscriber->setCustomerId($customerId);
                    $checkSubscriber->setDateOfBirth($customer->getDob());
                    $checkSubscriber->setStatus(\Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED);
                    $checkSubscriber->save();
                }
            } else {
                if ($checkSubscriber->getId()) {
                    switch ($from) {
                        case 'checkout':
                        case 'subscription':
                        case 'registration':
                            break;
                        default:
                            $checkSubscriber->unsubscribe();
                            break;
                    }
                }
            }
        }

        if ($policy = $this->policyRepository->getCurrentPolicy()) {
            try {
                /** @var WithConsent $withConsent */
                $withConsent = $this->consentFactory->create();
                $privacyPolicyVersionValue = $consentModel->getPrivacyLinkType() === ConsentLinkType::PRIVACY_POLICY ?
                    $policy->getPolicyVersion() : self::CMS_PAGE;
                $withConsent->setPolicyVersion($privacyPolicyVersionValue);
                $withConsent->setGotFrom($from);
                $withConsent->setWebsiteId($this->storeManager->getWebsite()->getId());
                $withConsent->setIp($this->visitor->getRemoteIp());
                $withConsent->setCustomerId($customerId);
                $withConsent->setCustomerEmail($email);
                $withConsent->setAction($consentModel->isConsentAccepted());
                $withConsent->setConsentCode($consentModel->getConsentCode());
                $this->withConsentRepository->save($withConsent);
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }
        }
    }
}
