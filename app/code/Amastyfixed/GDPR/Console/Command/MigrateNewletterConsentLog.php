<?php
namespace Amastyfixed\GDPR\Console\Command;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Api\WithConsentRepositoryInterface;
use Amasty\Gdpr\Model\ResourceModel\WithConsent as WithConsentResource;
use Amasty\Gdpr\Model\Source\ConsentLinkType;
use Amasty\Gdpr\Model\WithConsentFactory;
use Amasty\Gdpr\Model\Consent\ResourceModel\CollectionFactory;
use Amasty\Gdpr\Model\Consent;
use Magento\Framework\Console\Cli;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as SubscriberCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;


class MigrateNewletterConsentLog extends Command
{
    /**
     * Full tag for consent id
     */
    const CONSENT_ID_TAG = 'consent_id';

    /**
     * Shortcut tag for consent id
     */
    const CONSENT_ID_SHORTCUT_TAG = 'id';

    /**
     * Action code 1 means accept consent, 0 means decline consent
     */
    const ACTION_ACCEPT = 1;

    /**
     * Consent checkbox position
     */
    const POSITION = 'subscription';

    const CMS_PAGE = 'CMS Page';

    /**
     * @var CustomerFactory
     */
    protected $customerRepositoryInterface;

    /**
     * @var WithConsentRepositoryInterface
     */
    protected $withConsentRepository;

    /**
     * @var WithConsentFactory
     */
    protected $consentFactory;

    /**
     * @var WithConsentResource
     */
    protected $withConsent;

    protected $collectionFactory;

    protected $_storeManager;

    protected $policyRepository;

    protected $subscriberFactory;

    protected $subscriberCollection;

    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        WithConsentRepositoryInterface $withConsentRepository,
        WithConsentFactory $consentFactory,
        WithConsentResource $withConsent,
        CollectionFactory $collectionFactory,
        PolicyRepositoryInterface $policyRepository,
        StoreManagerInterface $storeManager,
        SubscriberFactory $subscriberFactory,
        SubscriberCollection $subscriberCollection
    )
    {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->withConsentRepository = $withConsentRepository;
        $this->consentFactory = $consentFactory;
        $this->withConsent = $withConsent;
        $this->collectionFactory = $collectionFactory;
        $this->policyRepository = $policyRepository;
        $this->subscriberFactory= $subscriberFactory;
        $this->subscriberCollection = $subscriberCollection;
        $this->_storeManager = $storeManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('migrate:newletter-consent-log')
             ->setDescription('Migrate existing user who already subscribed/unsubscribed for doing consent log')
             ->setDefinition([
                new InputOption(
                    self::CONSENT_ID_TAG, self::CONSENT_ID_SHORTCUT_TAG,
                    InputOption::VALUE_REQUIRED,
                    __("Consent ID")
                )
            ]);
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $subscriberCollection = $this->subscriberFactory->create()->getCollection();

        $progressBar = new ProgressBar($output, $subscriberCollection->count());

        $policy = $this->policyRepository->getCurrentPolicy();
        $storeId = $this->_storeManager->getStore()->getId();
        $consentsCollection = $this->collectionFactory
            ->create()
            ->addStoreData($storeId)
            ->addFieldToFilter(Consent\Consent::ID, $input->getOption(self::CONSENT_ID_TAG))
            ->addFieldToFilter(Consent\ConsentStore\ConsentStore::LOG_THE_CONSENT, true)
            ->addFieldToFilter(Consent\ConsentStore\ConsentStore::IS_ENABLED, true);

        if($consentsCollection->getSize()) {
            $consent = $consentsCollection->getFirstItem();
            foreach ($subscriberCollection as $subscriber) {
                //print_r($subscriber->getData());
                $action   = ($subscriber->getStatus() == 1)? 1 : 0;
                $subscriber->setConsentCode($consent->getConsentCode());
                $subscriber->setAction(($action)?'Accept':'Decline');

                $withConsent = $this->consentFactory->create();
                $privacyPolicyVersionValue = $consent->getPrivacyLinkType() === ConsentLinkType::PRIVACY_POLICY ?
                    $policy->getPolicyVersion() : self::CMS_PAGE;
                $withConsent->setCustomerId($subscriber->getCustomerId())
                    ->setCustomerEmail($subscriber->getSubscriberEmail())
                    ->setWebsiteId($this->_storeManager->getWebsite()->getId())
                    ->setDateConsented($subscriber->getChangeStatusAt())
                    ->setPolicyVersion($privacyPolicyVersionValue)
                    ->setGotFrom(self::POSITION)
                    ->setAction($action)
                    ->setConsentCode($consent->getConsentCode());

                try {
                    $this->withConsentRepository->save($withConsent);
                } catch (\Exception $exception) {
                    throw new CouldNotSaveException(__($exception->getMessage()));
                }

                $progressBar->advance();
            }
            $subscriberCollection->save();
            $progressBar->finish();
        }else{
            $output->writeln('Consent ID does not exist or please check it has disabled or not');
        }

        return Cli::RETURN_SUCCESS;
    }
}