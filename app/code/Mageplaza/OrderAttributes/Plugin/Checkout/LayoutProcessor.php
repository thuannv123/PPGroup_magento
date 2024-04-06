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

namespace Mageplaza\OrderAttributes\Plugin\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor as LayoutProcessorCore;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir;
use Mageplaza\OrderAttributes\Helper\Data;

/**
 * Class LayoutProcessor
 * @package Mageplaza\OrderAttributes\Plugin\Checkout
 */
class LayoutProcessor
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var ReadInterface
     */
    protected $mediaDirectory;
    /**
     * @var Dir
     */
    protected $directory;
    /**
     * @var File
     */
    protected $fileDriver;

    /**
     * LayoutProcessor constructor.
     *
     * @param Dir $directory
     * @param Data $helperData
     * @param File $fileDriver
     */
    public function __construct(
        Dir $directory,
        Data $helperData,
        File $fileDriver
    ) {
        $this->directory  = $directory;
        $this->fileDriver = $fileDriver;
        $this->helperData = $helperData;
    }

    /**
     * @param LayoutProcessorCore $subject
     * @param array $jsLayout
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function afterProcess(
        LayoutProcessorCore $subject,
        array $jsLayout
    ) {
        $steps = $this->helperData->getStepCollection();
        if (isset($jsLayout['components']['checkout']['children']['steps']['children'])) {
            foreach ($steps as $step) {
                $stepCode = $step->getData('code');
                $jsLayout['components']['checkout']['children']['steps']['children'][$stepCode]
                          = [
                                'component' => 'Mageplaza_OrderAttributes/js/view/step/mp-custom-step-' . $stepCode,
                                'children'  => [
                                    'mpOrderAttributes' => [
                                        'component'   => 'Mageplaza_OrderAttributes/js/view/attributes',
                                        'displayArea' => 'mpOrderAttributes',
                                        'scope'       => $stepCode,
                                        'config'      => [
                                            'template' => 'Mageplaza_OrderAttributes/step/container/mp-fieldset',
                                        ],
                                    ]
                                ],
                                'config'    => [
                                    'template'  => $this->getTemplate()
                                ]
                          ];
                try {
                    $this->helperData->createJsFileStep($step);
                } catch (FileSystemException $e) {
                    $this->helperData->getLogger()->error($e->getMessage());
                }

            }
        }
        if ($this->helperData->isOscPage()) {
            $jsLayout['components']['checkout']['children']['osc-steps-after'] = [
                'component' => 'Mageplaza_OrderAttributes/js/view/osc-steps',
                'config'    => [
                    'template' => 'Mageplaza_OrderAttributes/osc-steps-after'
                ]
            ];
            $jsLayout['components']['checkout']['children']['osc-steps-before'] = [
                'component' => 'Mageplaza_OrderAttributes/js/view/osc-steps',
                'config'    => [
                    'template' => 'Mageplaza_OrderAttributes/osc-steps-before'
                ]
            ];
        }

        return $jsLayout;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->helperData->isOscPage() ?
            'Mageplaza_OrderAttributes/step/osc-page' : 'Mageplaza_OrderAttributes/step/mp-custom';
    }
}
