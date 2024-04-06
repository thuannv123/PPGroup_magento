<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Social Login Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SocialLogin\Plugin\AdvancedReview\ViewModel\Reviews\Product\View\ListView\WriteReviewButton;

use Amasty\AdvancedReview\ViewModel\Reviews\Product\View\ListView\WriteReviewButton as WriteReviewButton;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Module\Manager;
use Magento\Framework\UrlInterface;

class AllowWriteReviewButton
{
    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var bool
     */
    private $shouldSaveUrl = false;

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        SessionFactory $sessionFactory,
        UrlInterface $urlBuilder,
        Manager $moduleManager
    ) {
        $this->sessionFactory = $sessionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param WriteReviewButton $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsCanRender($subject, bool $result): bool
    {
        $this->shouldSaveUrl = $result;
        return true;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param WriteReviewButton $subject
     * @param bool $result
     * @return bool
     */
    public function afterGetButtonUrl($subject, string $result): string
    {
        if (!$this->moduleManager->isEnabled('Amasty_JetTheme') && !$this->shouldSaveUrl) {
            return $this->urlBuilder->getUrl('customer/account/login');
        }

        return $result;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    private function getCustomerSession()
    {
        return $this->sessionFactory->create();
    }
}
