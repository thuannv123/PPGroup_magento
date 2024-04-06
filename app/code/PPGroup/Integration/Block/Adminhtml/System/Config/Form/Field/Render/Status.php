<?php

namespace PPGroup\Integration\Block\Adminhtml\System\Config\Form\Field\Render;

use Magento\Framework\View\Element\Context;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory as OrderStatusCollection;

class Status extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * Method List
     *
     * @var array
     */
    protected $statusCollectionFactory;


    /**
     * Constructor
     *
     * @param Context $context Context
     * @param OrderStatusCollection $statusCollectionFactory
    Status Collection Factory
     * @param array $data Data
     */
    public function __construct(
        Context $context,
        OrderStatusCollection $statusCollectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->statusCollectionFactory = $statusCollectionFactory;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $statuses = $this->statusCollectionFactory->create()->load();

            foreach ($statuses as $status) {
                $this->addOption($status->getStatus(), $status->getLabel());
            }
        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value Value
     *
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
