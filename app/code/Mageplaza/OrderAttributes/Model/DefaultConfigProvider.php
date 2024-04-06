<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_OrderAttributes
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\OrderAttributes\Model;

use Exception;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\IteratorFactory;
use Mageplaza\OrderAttributes\Helper\Data;
use Mageplaza\OrderAttributes\Model\Config\Source\PositionStep;
use Mageplaza\OrderAttributes\Model\Config\Source\Status;
use Mageplaza\OrderAttributes\Model\StepFactory;
use Psr\Log\LoggerInterface;

/**
 * Class DefaultConfigProvider
 * @package Mageplaza\OrderAttributes\Model
 */
class DefaultConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var ResourceModel\Attribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var IteratorFactory
     */
    private $iteratorFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StepFactory
     */
    public $stepFactory;

    /**
     * @var array
     */
    private $availableSteps = [];

    /**
     * DefaultConfigProvider constructor.
     *
     * @param ResourceModel\Attribute\CollectionFactory $collectionFactory
     * @param Data $dataHelper
     * @param IteratorFactory $iteratorFactory
     * @param LoggerInterface $logger
     * @param \Mageplaza\OrderAttributes\Model\StepFactory $stepFactory
     */
    public function __construct(
        ResourceModel\Attribute\CollectionFactory $collectionFactory,
        Data $dataHelper,
        IteratorFactory $iteratorFactory,
        LoggerInterface $logger,
        StepFactory $stepFactory
    ) {
        $this->dataHelper        = $dataHelper;
        $this->collectionFactory = $collectionFactory;
        $this->iteratorFactory   = $iteratorFactory;
        $this->logger            = $logger;
        $this->stepFactory       = $stepFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        if (!$this->dataHelper->isEnabled()) {
            return [];
        }

        return ['mpOaConfig' => $this->getAttributeData()];
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getAttributeData()
    {
        $data = [
            'isOscPage'       => $this->dataHelper->isOscPage(),
            'attributeDepend' => [],
            'shippingDepend'  => [],
            'countryDepend'   => [],
            'contentType'     => [],
            'tinymceConfig'   => $this->dataHelper->getTinymceConfig()
        ];

        $attributes = $this->dataHelper->getFilteredAttributes();
        foreach ($attributes as $attribute) {
            $frontendInput = $attribute->getFrontendInput();

            if ($attribute->getFieldDepend() || in_array($frontendInput, ['select', 'select_visual', 'boolean'])) {
                $data['attributeDepend'][] = $attribute->getData();
            }

            if ($attribute->getShippingDepend()) {
                $carriers     = $this->dataHelper->getShippingMethods();
                $carrierCodes = [];
                foreach ($carriers as $carrier) {
                    foreach ($carrier['value'] as $child) {
                        $carrierCodes[] = $child['value'];
                    }
                }

                foreach (explode(',', $attribute->getShippingDepend()) as $shippingMethod) {
                    if (in_array($shippingMethod, $carrierCodes)) {
                        $data['shippingDepend'][] = $attribute->getData();
                        break;
                    }
                }
            }

            if ($attribute->getCountryDepend()) {
                $data['countryDepend'][] = $attribute->getData();
            }

            if ($frontendInput === 'textarea_visual') {
                $data['contentType'][] = $attribute->getData();
            }
        }

        $iterator       = $this->iteratorFactory->create();
        $stepCollection = $this->stepFactory->create()->getCollection();
        $stepCollection->addFieldToFilter(
            'position',
            ['in' => [PositionStep::BEFORE_SHIPPING, PositionStep::AFTER_SHIPPING]]
        )->addFieldToFilter('status', Status::ENABLE);
        $iterator->walk($stepCollection->getSelect(), [[$this, 'getStepCode']]);

        try {
            $data['availableSteps'] = $this->availableSteps;
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $data;
    }

    /**
     * @param $args
     * @return void
     */
    public function getStepCode($args)
    {
        $stepData = $args['row'];
        $this->availableSteps[] = $stepData['code'];
    }
}
