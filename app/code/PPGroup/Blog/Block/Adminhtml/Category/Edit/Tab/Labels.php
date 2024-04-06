<?php

namespace PPGroup\Blog\Block\Adminhtml\Category\Edit\Tab;

use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Config\Model\Config\Source\Design\Robots;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use PPGroup\Blog\Helper\Data as HelperData;

class Labels extends Generic implements TabInterface
{
    /**
     * Wysiwyg config
     *
     * @var Config
     */
    protected $wysiwygConfig;

    /**
     * Country options
     *
     * @var Yesno
     */
    protected $booleanOptions;

    /**
     * @var Enabledisable
     */
    protected $enableDisable;

    /**
     * @var Robots
     */
    protected $metaRobotsOptions;

    /**
     * @var Store
     */
    protected $systemStore;

    /**
     *
     * @var HelperData
     */
    protected $helper;

    /**
     * Category constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Config $wysiwygConfig
     * @param Yesno $booleanOptions
     * @param Enabledisable $enableDisable
     * @param Robots $metaRobotsOptions
     * @param Store $systemStore
     * @param HelperData $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Yesno $booleanOptions,
        Enabledisable $enableDisable,
        Robots $metaRobotsOptions,
        Store $systemStore,
        HelperData $helper,
        array $data = []
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->booleanOptions = $booleanOptions;
        $this->enableDisable = $enableDisable;
        $this->metaRobotsOptions = $metaRobotsOptions;
        $this->systemStore = $systemStore;
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _prepareForm()
    {
        /** @var \Mageplaza\Blog\Model\Category $category */
        $category = $this->_coreRegistry->registry('category');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('category_');
        $form->setFieldNameSuffix('category');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Manager Labels'),
            'class' => 'fieldset-wide-labels'
        ]);
        $storeIds = $this->_storeManager->getStores();

        if($category->getId()){
            $values = $category->load($category->getId())->getData('labels');
            $arr = $this->helper->jsonDecodeData($values);
            foreach ($storeIds as $storeId) {
                $id = $storeId->getId();
                $fieldset->addField("label[$id]", 'text', [
                    'name' => $storeId->getName(),
                    'label' => __($storeId->getName()),
                    'title' => __($storeId->getName()),
                    'required' => false,
                    'value' => $arr[$id],
                ]);
            }
        }else{
            foreach ($storeIds as $storeId) {
                $id = $storeId->getId();
                $fieldset->addField("label[$id]", 'text', [
                    'name' => $storeId->getName(),
                    'label' => __($storeId->getName()),
                    'title' => __($storeId->getName()),
                    'required' => false,
                ]);
            }
        }
        

        $form->addValues($category->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Category');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}