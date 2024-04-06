<?php

namespace PPGroup\Gdpr\Plugin\Model\Policy\DataProvider;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Model\Policy\DataProvider\PolicyPopupDataProvider;
use Amasty\Gdpr\Model\ResourceModel\WithConsent;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Zend_Db_Expr;

class PolicyPopupDataProviderPlugin
{
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    /**
     * PolicyPopupDataProviderPlugin constructor.
     * @param ResourceConnection $resource
     * @param PolicyRepositoryInterface $policyRepository
     * @param Session $customerSession
     */
    public function __conStruct(
        ResourceConnection $resource,
        PolicyRepositoryInterface $policyRepository,
        Session $customerSession
    ) {
        $this->resource = $resource;
        $this->customerSession = $customerSession;
        $this->policyRepository = $policyRepository;
    }
    public function afterGetData(
        PolicyPopupDataProvider $subject,
        $result
    ) {
        $customerId = $this->customerSession->getCustomerId();
        if ($customerId && $this->getConsentLog($customerId) && $policy = $this->policyRepository->getCurrentPolicy()) {
            $result['show'] = false;
        }
        return $result;
    }

    /**
     * @param $customerId
     * @return int
     */
    protected function getConsentLog($customerId)
    {
        $policy = $this->policyRepository->getCurrentPolicy();
        $logTable = $this->resource->getTableName(WithConsent::TABLE_NAME);
        $select = $this->resource->getConnection()->select()
            ->from($logTable, new Zend_Db_Expr('count(*)'))
            ->where('customer_id=?', $customerId)
            ->where('policy_version=?', $policy->getPolicyVersion())
            ->where('action="1"');
        return (int)$this->resource->getConnection()->fetchOne($select);
    }
}
