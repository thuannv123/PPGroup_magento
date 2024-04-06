<?php

namespace PPGroup\ConfigurableProduct\Plugin;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use \Magento\Framework\Json\EncoderInterface;
use \Magento\Framework\Json\DecoderInterface;

class ConfigurableProductViewBlockPlugin
{
    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     *
     * @var  DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * ConfigurableProductViewBlockPlugin constructor.
     * @param EncoderInterface $jsonEncoder
     * @param DecoderInterface $jsonDecoder
     */
    public function __construct(
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder
    )
    {
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * Composes configuration for js
     *
     * @param Configurable $subject
     * @param string $result
     * @return string
     */
    public function afterGetJsonConfig(
        Configurable $subject,
        string $result
    )
    {
        $result = $this->jsonDecoder->decode($result);
        $result['stock'] = $subject->getProduct()->isSalable();
        return $this->jsonEncoder->encode($result);
    }
}
