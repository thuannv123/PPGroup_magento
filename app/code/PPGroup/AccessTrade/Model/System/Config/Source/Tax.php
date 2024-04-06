<?php
namespace PPGroup\AccessTrade\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Tax implements OptionSourceInterface {

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
                'value' => \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX,
                'label' => __('Excluding Tax'),
            ];
            $this->_options[] = [
                'value' => \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX,
                'label' => __('Including Tax'),
            ];
        }
        return $this->_options;
    }
}
