<?php
namespace Amastyfixed\GDPR\Plugin\Observer;
use Magento\Customer\Model\Session;

class AcceptConsents
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(
        Session $session
    ) {
        $this->session = $session;
    }

    public function afterExecute()
    {
        $this->session->unsCustomerEmail();
    }
}