<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Invisible reCaptcha for Magento 2
 */

namespace Amasty\InvisibleCaptcha\Model;

use Amasty\Base\Model\GetCustomerIp;
use Amasty\Base\Model\Serializer;
use Amasty\InvisibleCaptcha\Model\Config\Source\CaptchaVersion;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;

class Captcha
{
    /**
     * Google URL for checking captcha response
     */
    public const GOOGLE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var GetCustomerIp
     */
    private $getCustomerIp;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        Curl $curl,
        Session $session,
        GetCustomerIp $getCustomerIp,
        ConfigProvider $configProvider,
        LoggerInterface $logger,
        Serializer $serializer
    ) {
        $this->curl = $curl;
        $this->session = $session;
        $this->getCustomerIp = $getCustomerIp;
        $this->configProvider = $configProvider;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * Check is need to show captcha
     *
     * @return bool
     */
    public function isNeedToShowCaptcha(): bool
    {
        if ($this->configProvider->isEnabled() && $this->configProvider->isConfigured()) {
            try {
                $customerGroupId = $this->session->getCustomerGroupId();
            } catch (LocalizedException $e) {
                $this->logger->error($e->getMessage());

                return false;
            }
            if ($customerGroupId == Group::NOT_LOGGED_IN_ID
                || !$this->configProvider->isEnabledForGuestsOnly()
            ) {
                if (!in_array($this->getCustomerIp->getCurrentIp(), $this->configProvider->getWhiteIps())) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verification of token by Google
     *
     * @param string|null $token
     * @return array
     */
    public function verify(?string $token): array
    {
        $verification = [
            'success' => false,
            'error' => __('No reCaptcha token.')
        ];
        if ($token) {
            $curlParams = [
                'secret' => $this->configProvider->getSecretKey(),
                'response' => $token
            ];

            try {
                $this->curl->write(
                    Request::METHOD_POST,
                    self::GOOGLE_VERIFY_URL,
                    '1.1',
                    [],
                    $curlParams
                );
                $googleResponse = $this->curl->read();
                $responseBody = Response::fromString($googleResponse)->getBody();
                $googleAnswer = $this->serializer->unserialize($responseBody);
                if (array_key_exists('success', $googleAnswer)) {
                    if (isset($googleAnswer['score'])
                        && $this->configProvider->getCaptchaVersion() === CaptchaVersion::VERSION_3
                        && $googleAnswer['score'] < $this->configProvider->getCaptchaScore()
                    ) {
                        $verification['error'] = $this->configProvider->getConfigErrorMessage();
                        $verification['success'] = false;
                    } elseif ($googleAnswer['success']) {
                        $verification['success'] = true;
                    } elseif (array_key_exists('error-codes', $googleAnswer)) {
                        $verification['error'] = $this->getErrorMessage($googleAnswer['error-codes'][0]);
                    }
                }
            } catch (\Exception $e) {
                $verification['error'] = __($e->getMessage());
            }
        }

        return $verification;
    }

    /**
     * @param string $errorCode
     * @return Phrase
     */
    private function getErrorMessage(string $errorCode): Phrase
    {
        $errorCodesGoogle = [
            'missing-input-secret' => __('The secret parameter is missing.'),
            'invalid-input-secret' => __('The secret parameter is invalid or malformed.'),
            'missing-input-response' => __('The response parameter is missing.'),
            'invalid-input-response' => __('The response parameter is invalid or malformed.'),
            'bad-request' => __('The request is invalid or malformed.'),
            'timeout-or-duplicate' => __(
                'The response is no longer valid: either is too old or has been used previously.'
            )
        ];

        if (array_key_exists($errorCode, $errorCodesGoogle)) {
            return $errorCodesGoogle[$errorCode];
        }

        return __('Something is wrong.');
    }
}
