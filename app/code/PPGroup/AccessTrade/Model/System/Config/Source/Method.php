<?php
namespace PPGroup\AccessTrade\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Method implements OptionSourceInterface {

    const TRACKING_TAG = 1;
    const TRACKING_API = 2;
    /**
     * @var array
     */
    protected $_options;

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = [];
            $this->_options[] = [
                'value' => self::TRACKING_TAG,
                'label' => __('Tracking Tags'),
            ];
            $this->_options[] = [
                'value' => self::TRACKING_API,
                'label' => __('Tracking API'),
            ];
        }
        return $this->_options;
    }
}
