<?php

namespace Amastyfixed\GDPR\Observer;

use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session as CustomerSession;
use Amastyfixed\GDPR\Helper\Data as GdprHelper;

class SaveConsentLogSubscribe implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;
    private $helper;
    private $customerSession;

    public function __construct(
        RequestInterface $request,
        GdprHelper $helper,
        CustomerSession $customerSession
    )
    {
        $this->request = $request;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
    }

    /**
     * @param Observer $observer
     *
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $this->customerSession->setCustomerEmail($observer->getEvent()->getSubscriber()->getSubscriberEmail());
        $consentsCodes = (array)$this->request->getParam(RegistryConstants::CONSENTS, []);

        $checkboxcodes = explode(',', $this->helper->getCheckBoxCode());

        foreach ($consentsCodes as $key => $value) {
            if (in_array($key, $checkboxcodes)) {
                $subscriber = $observer->getEvent()->getSubscriber();
                $subscriber->setConsentCode($key);
                $subscriber->setAction(($value) ? 'Accept' : 'Decline');
                break;
            }
        }
    }
}