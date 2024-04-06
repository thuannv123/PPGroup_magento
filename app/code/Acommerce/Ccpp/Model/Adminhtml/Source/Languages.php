<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acommerce\Ccpp\Model\Adminhtml\Source;

/**
 * Class Languages
 */
class Languages implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * {@inheritdoc}
     */
    public function toOptionArray() {
        return [
            [
                'value' => 'en',
                'label' => __('English')
            ],
            [
                'value' => 'th',
                'label' => __('Thailand')
            ],
            [
                'value' => 'ja',
                'label' => __('Japanese')
            ],
        ];
    }
}
