<?php

namespace PPGroup\Gdpr\Plugin\Model;

use Amasty\Gdpr\Model\Anonymizer;
use Amasty\Gdpr\Model\Config;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

class AnonymizerPlugin
{
    /**
     * @var Config
     */
    private $configProvider;
    /**
     * @var SenderResolverInterface
     */
    protected $senderResolver;
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    public function __construct(
        Config $configProvider,
        TransportBuilder $transportBuilder,
        SenderResolverInterface $senderResolver
    ) {
        $this->configProvider = $configProvider;
        $this->transportBuilder = $transportBuilder;
        $this->senderResolver = $senderResolver;
    }

    /**
     * @param Anonymizer $subject
     * @param callable $proceed
     * @param $configPath
     * @param $realEmail
     * @param $customerName
     * @param Customer $customer
     * @throws LocalizedException
     * @throws MailException
     */
    public function aroundSendConfirmationEmail(
        Anonymizer $subject,
        callable $proceed,
        $configPath,
        $realEmail,
        $customerName,
        $customer
    ) {
        $template = $this->configProvider->getConfirmationEmailTemplate($configPath, $customer->getStoreId());

        $sender = $this->configProvider->getConfirmationEmailSender($configPath, $customer->getStoreId());

        $replyTo = $this->configProvider->getConfirmationEmailReplyTo($configPath, $customer->getStoreId());
        if (!trim($replyTo)) {
            $result = $this->senderResolver->resolve($sender);
            $replyTo = $result['email'];
        }

        $transport = $this->transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            [
                'area'  => Area::AREA_FRONTEND,
                'store' => $customer->getStoreId()
            ]
        )->setTemplateVars(
            [
                'anonymousEmail' => $customer->getEmail(),
                'customerName' => $customerName
            ]
        )->setFrom(
            $sender
        )->addTo(
            $realEmail,
            $customerName
        )->setReplyTo(
            $replyTo
        )->getTransport();

        $transport->sendMessage();
    }
}
