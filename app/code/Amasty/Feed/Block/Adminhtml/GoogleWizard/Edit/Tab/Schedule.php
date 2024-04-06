<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Block\Adminhtml\GoogleWizard\Edit\Tab;

class Schedule extends TabGeneric
{
    /**
     * @var \Amasty\Feed\Model\FeedFactory
     */
    private $feedFactory;

    /**
     * @var \Amasty\Feed\Model\Config\Source\ExecuteModeList
     */
    private $executeModeList;

    /**
     * @var \Amasty\Feed\Model\CronProvider
     */
    private $cronProvider;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Feed\Model\RegistryContainer $registryContainer,
        \Amasty\Feed\Model\Config\Source\ExecuteModeList $executeModeList,
        \Amasty\Feed\Model\CronProvider $cronProvider,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $registryContainer, $data);
        $this->executeModeList = $executeModeList;
        $this->cronProvider = $cronProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Step 6: Schedule Settings');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Step 6: Schedule Settings');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareNotEmptyForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('feed_');

        $fieldset = $form->addFieldset('schedule_fieldset', ['legend' => __('Schedule')]);

        $executeMode = $fieldset->addField(
            'execute_mode',
            'select',
            [
                'label' => __('Generate feed'),
                'name' => 'execute_mode',
                'options' => $this->executeModeList->toArray()
            ]
        );

        $cronDay = $fieldset->addField(
            'cron_day',
            'multiselect',
            [
                'label' => __('Day'),
                'name' => 'cron_day',
                'required' => true,
                'values' => $this->cronProvider->getOptionWeekdays()
            ]
        );

        $cronTime = $fieldset->addField(
            'cron_time',
            'multiselect',
            [
                'label' => __('Time'),
                'name' => 'cron_time',
                'required' => true,
                'values' => $this->cronProvider->getCronTime()
            ]
        );

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Form\Element\Dependence::class
            )->addFieldMap(
                $executeMode->getHtmlId(),
                $executeMode->getName()
            )->addFieldMap(
                $cronDay->getHtmlId(),
                $cronDay->getName()
            )->addFieldMap(
                $cronTime->getHtmlId(),
                $cronTime->getName()
            )->addFieldDependence(
                $cronTime->getName(),
                $executeMode->getName(),
                'schedule'
            )->addFieldDependence(
                $cronDay->getName(),
                $executeMode->getName(),
                'schedule'
            )
        );

        $this->setForm($form);

        return $this;
    }
}
