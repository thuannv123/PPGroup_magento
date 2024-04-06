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
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab;

use DateTimeZone;
use Exception;
use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Cms\Model\Page\Source\PageLayout as BasePageLayout;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Config\Model\Config\Source\Design\Robots;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\System\Store;
use Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer\Category;
use Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer\Tag;
use Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer\Topic;
use Mageplaza\Blog\Helper\Image;
use Mageplaza\Blog\Model\Config\Source\Author;
use Mageplaza\Blog\Model\Config\Source\AuthorStatus;
use Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;
use Mageplaza\Blog\Model\ResourceModel\PostCustom\CollectionFactory as PostCustomCollectionFactory;

/**
 * Class Post
 * @package Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab
 */
class PostCustom extends Generic implements TabInterface
{
    /**
     * Wysiwyg config
     *
     * @var Config
     */
    public $wysiwygConfig;

    /**
     * Country options
     *
     * @var Yesno
     */
    public $booleanOptions;

    /**
     * @var Robots
     */
    public $metaRobotsOptions;

    /**
     * @var Store
     */
    public $systemStore;

    /**
     * @var Session
     */
    protected $authSession;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var BasePageLayout
     */
    protected $_layoutOptions;

    /**
     * @var Author
     */
    protected $_author;

    /**
     * @var AuthorStatus
     */
    protected $_status;

    protected $PostCollectionFactory;


    protected $collectionFactory;
    /**
     * Post constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Session $authSession
     * @param DateTime $dateTime
     * @param BasePageLayout $layoutOption
     * @param FormFactory $formFactory
     * @param Config $wysiwygConfig
     * @param Yesno $booleanOptions
     * @param Robots $metaRobotsOptions
     * @param Store $systemStore
     * @param Image $imageHelper
     * @param Author $author
     * @param AuthorStatus $status
     * @param PostCollectionFactory $PostCollectionFactory
     * @param PostCustomCollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Session $authSession,
        DateTime $dateTime,
        BasePageLayout $layoutOption,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Yesno $booleanOptions,
        Robots $metaRobotsOptions,
        Store $systemStore,
        Image $imageHelper,
        Author $author,
        AuthorStatus $status,
        PostCollectionFactory $PostCollectionFactory,
        PostCustomCollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->booleanOptions = $booleanOptions;
        $this->metaRobotsOptions = $metaRobotsOptions;
        $this->systemStore = $systemStore;
        $this->authSession = $authSession;
        $this->_date = $dateTime;
        $this->_layoutOptions = $layoutOption;
        $this->imageHelper = $imageHelper;
        $this->_author = $author;
        $this->_status = $status;
        $this->PostCollectionFactory = $PostCollectionFactory;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function _prepareForm()
    {
        $post = $this->_coreRegistry->registry('mageplaza_blog_post');
        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('post_');
        $form->setFieldNameSuffix('posts');
        foreach ($this->_storeManager->getStores() as $store) {
            $result = [];
            if($post->getId()){
                $collection2 = $this->collectionFactory->create();
                $collection2->addAttributeToFilter('store_id',$store->getId());
                $result = $collection2->addAttributeToFilter('post_id',$post->getId())->getData();
            }
            $name               = ($result == [])? '' :  $result[0]['name'];
            $short_description  = ($result == [])? '' :  $result[0]['short_description'];
            $post_content       = ($result == [])? '' :  $result[0]['post_content'];
            
            $fieldset = $form->addFieldset('base_fieldset'.$store->getName(), [
                'legend' => __('Custom Post '.$store->getName()),
                'class' => 'fieldset-wide',
                'expanded'=> true,
            ]);
            $fieldset->addField('name'.$store->getName(), 'text', [
                'name' => 'name'.$store->getName(),
                'label' => __('Name '.$store->getName()),
                'title' => __('Name'.$store->getName()),
                'value'=> $name,
            ]);
            $fieldset->addField('short_description'.$store->getName(), 'textarea', [
                'name' => 'short_description'.$store->getName(),
                'label' => __('Short Description'),
                'title' => __('Short Description'),
                'value'=> $short_description,
                
            ]);
            $fieldset->addField('post_content'.$store->getName(), 'editor', [
                'name' => 'post_content'.$store->getName(),
                'label' => __('Content'),
                'title' => __('Content'),
                'config' => $this->wysiwygConfig->getConfig([
                    'add_variables' => false,
                    'add_widgets' => true,
                    'add_directives' => true
                ]),
                'value'=> $post_content,
            ]);
        }
       

        if ($this->_request->getParam('duplicate')) {
            $fieldset->addField('duplicate', 'hidden', [
                'name' => 'duplicate',
                'value' => 1
            ]);
        }

        $form->addValues($post->getData());
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
        return __('Custom Post');
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
