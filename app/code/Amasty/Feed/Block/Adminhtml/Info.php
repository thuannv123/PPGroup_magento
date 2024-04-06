<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Element\BlockInterface;

class Info extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory
     */
    private $cronFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Reader
     */
    private $reader;

    /**
     * @var \Amasty\Base\Model\MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var Module
     */
    private $moduleHelper;

    /**
     * @var BlockInterface
     */
    private $fieldRenderer;

    public function __construct(
        \Amasty\Base\Model\MagentoVersion $magentoVersion,
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $cronFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\DeploymentConfig\Reader $reader,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Amasty\Base\Helper\Module $moduleHelper,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);

        $this->layoutFactory = $layoutFactory;
        $this->cronFactory = $cronFactory;
        $this->directoryList = $directoryList;
        $this->resourceConnection = $resourceConnection;
        $this->productMetadata = $productMetadata;
        $this->reader = $reader;
        $this->magentoVersion = $magentoVersion;
        $this->moduleHelper = $moduleHelper;
    }

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $html .= $this->getSystemTime($element);
        $html .= $this->getCronInfo($element);

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    private function _getFieldRenderer()
    {
        if (empty($this->fieldRenderer)) {
            $layout = $this->layoutFactory->create();

            $this->fieldRenderer = $layout->createBlock(
                Field::class
            );
        }

        return $this->fieldRenderer;
    }

    /**
     * @param AbstractElement $fieldset
     * @return string
     */
    private function getSystemTime($fieldset)
    {
        if (version_compare($this->magentoVersion->get(), '2.2', '>=')) {
            $time = $this->resourceConnection->getConnection()->fetchOne("select now()");
        } else {
            $time = $this->_localeDate->date()->format('Y-m-d H:i:s');
        }

        return $this->getFieldHtml($fieldset, 'mysql_current_date_time', __("Current Time"), $time);
    }

    /**
     * @param AbstractElement $fieldset
     * @return string
     */
    private function getCronInfo($fieldset)
    {
        $crontabCollection = $this->cronFactory->create();
        $crontabCollection->addFieldToFilter('job_code', ['eq' => 'amfeed_feed_refresh']);
        $crontabCollection->setOrder('schedule_id')->setPageSize(5);

        $knowledgeBaseUrl = 'https://amasty.com/knowledge-base/magento-cron.html' .
            "?utm_source=extension&utm_medium=link&utm_campaign=product-feed-m2-e-cron-faq";
        if ($this->moduleHelper->isOriginMarketplace()) {
            $knowledgeBaseUrl = "https://amasty.com/docs/doku.php?id=magento_2:cron-scheduler" .
                "&utm_source=extension&utm_medium=link&utm_campaign=cronscheduler_m2_guide";
        }

        if ($crontabCollection->count() === 0) {
            $value = '<div class="red">';
            $value .= __('No cron jobs found') . "</div>";
            $value .= "<a target='_blank' href='" . $knowledgeBaseUrl . "'>" . __("Learn more") . "</a>";
        } else {
            $value = '<table>';
            foreach ($crontabCollection as $crontabRow) {
                $value .=
                    '<tr>' .
                    '<td>' . $crontabRow['job_code'] . '</td>' .
                    '<td>' . $crontabRow['status'] . '</td>' .
                    '<td>' . $crontabRow['created_at'] . '</td>' .
                    '</tr>';
            }
            $value .= '</table>';
        }

        $label = __('Cron (Last 5)');

        return $this->getFieldHtml($fieldset, 'cron_configuration', $label, $value);
    }

    /**
     * @param AbstractElement $fieldset
     * @param string $fieldName
     * @param string $label
     * @param string $value
     * @return string
     */
    private function getFieldHtml($fieldset, $fieldName, $label = '', $value = '')
    {
        $field = $fieldset->addField($fieldName, 'label', [
            'name'  => 'dummy',
            'label' => $label,
            'after_element_html' => $value,
        ])->setRenderer($this->_getFieldRenderer());

        return $field->toHtml();
    }
}
