<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\Detection;

use Magento\Framework\App\RequestInterface;

class IsSearchPage
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        return in_array(
            $this->request->getModuleName(),
            ['sqli_singlesearchresult', 'catalogsearch']
        );
    }
}
