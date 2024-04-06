<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Social;

/**
 * Class Button
 */
class Button extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $resolverInterface;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Locale\ResolverInterface $resolverInterface,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->resolverInterface = $resolverInterface;
    }

    /**
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->resolverInterface->getLocale();
    }
}
