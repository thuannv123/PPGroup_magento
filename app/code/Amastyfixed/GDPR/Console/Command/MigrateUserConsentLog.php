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
use Magento\Customer\Model\CustomerFactory;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;

class MigrateUserConsentLog extends Command
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
    const POSITION = 'registration';

    const CMS_PAGE = 'CMS Page';

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

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

    public function __construct(
        CustomerFactory $customerFactory,
        WithConsentRepositoryInterface $withConsentRepository,
        WithConsentFactory $consentFactory,
        WithConsentResource $withConsent,
        CollectionFactory $collectionFactory,
        PolicyRepositoryInterface $policyRepository,
        StoreManagerInterface $storeManager
    )
    {
        $this->customerFactory = $customerFactory;
        $this->withConsentRepository = $withConsentRepository;
        $this->consentFactory = $consentFactory;
        $this->withConsent = $withConsent;
        $this->collectionFactory = $collectionFactory;
        $this->policyRepository = $policyRepository;
        $this->_storeManager = $storeManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('migrate:user-consent-log')
             ->setDescription('Migrate existing users to consent log table')
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
        $customerCollection = $this->customerFactory->create()->getCollection();
        $customerCollectionLength = $customerCollection->count();

        $progressBar = new ProgressBar($output, $customerCollectionLength);

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
            foreach ($customerCollection as $customer) {
                $withConsent = $this->consentFactory->create();
                $privacyPolicyVersionValue = $consent->getPrivacyLinkType() === ConsentLinkType::PRIVACY_POLICY ?
                    $policy->getPolicyVersion() : self::CMS_PAGE;
                //echo $consent->getConsentCode();
                $withConsent->setCustomerId($customer->getId())
                    ->setDateConsented($customer->getCreatedAt())
                    ->setWebsiteId($customer->getWebsiteId())
                    ->setPolicyVersion($privacyPolicyVersionValue)
                    ->setGotFrom(self::POSITION)
                    ->setAction(self::ACTION_ACCEPT)
                    ->setConsentCode($consent->getConsentCode())
                    ->setCustomerEmail($customer->getEmail());

                try {
                    $this->withConsentRepository->save($withConsent);
                } catch (\Exception $exception) {
                    throw new CouldNotSaveException(__($exception->getMessage()));
                }

                $progressBar->advance();
            }
            $progressBar->finish();
        }else{
            $output->writeln('Consent ID does not exist or please check it has disabled or not');
        }

        return Cli::RETURN_SUCCESS;
    }
}
