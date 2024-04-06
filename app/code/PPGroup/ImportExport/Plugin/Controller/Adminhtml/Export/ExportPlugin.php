<?php

namespace PPGroup\ImportExport\Plugin\Controller\Adminhtml\Export;

use Magento\ImportExport\Controller\Adminhtml\Export\Export;
use Magento\ImportExport\Model\Export\Consumer;
use Magento\ImportExport\Model\Export\Entity\ExportInfoFactory;

class ExportPlugin
{
    /**
     * @var ExportInfoFactory
     */
    private $exportInfoFactory;
    /**
     * @var Consumer
     */
    private $consummer;

    /**
     * ExportPlugin constructor.
     * @param Consumer $consumer
     * @param ExportInfoFactory|null $exportInfoFactory
     */
    public function __construct(
        Consumer $consumer,
        ExportInfoFactory $exportInfoFactory = null
    ) {
        $this->consummer = $consumer;
        $this->exportInfoFactory = $exportInfoFactory ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(
                ExportInfoFactory::class
            );
    }

    /**
     * @param Export $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(
        Export $subject,
        $result
    ) {
        $params = $subject->getRequest()->getParams();

        if (!array_key_exists('skip_attr', $params)) {
            $params['skip_attr'] = [];
        }
        /** @var ExportInfoFactory $dataObject */
        $dataObject = $this->exportInfoFactory->create(
            $params['file_format'],
            $params['entity'],
            $params['export_filter'],
            $params['skip_attr']
        );

        $this->consummer->process($dataObject);
        return $result;
    }
}
