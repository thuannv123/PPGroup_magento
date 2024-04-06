<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Social Login Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SocialLogin\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class StoreResolver
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        StoreManagerInterface $storeManager,
        RequestInterface $request
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    public function getStore(): StoreInterface
    {
        $storeId = $this->request->getParam(ScopeInterface::SCOPE_STORE);

        if (!$storeId) {
            $websiteId = $this->request->getParam(ScopeInterface::SCOPE_WEBSITE);
            $storeId = $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
        }

        return $this->storeManager->getStore($storeId);
    }
}
