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

namespace Mageplaza\OrderAttributes\Helper;

use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Form\Filter\FilterInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Module\Dir;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\DesignInterface;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Shipping\Model\Config as CarrierConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\OrderAttributes\Model\Attribute;
use Mageplaza\OrderAttributes\Model\Config\Source\PositionStep;
use Mageplaza\OrderAttributes\Model\Config\Source\Status;
use Mageplaza\OrderAttributes\Model\Step;
use Mageplaza\OrderAttributes\Model\StepFactory;
use Mageplaza\OrderAttributes\Model\OrderFactory;
use Mageplaza\OrderAttributes\Model\ResourceModel\Attribute\Collection;
use Mageplaza\OrderAttributes\Model\ResourceModel\Attribute\CollectionFactory;
use Mageplaza\OrderAttributes\Model\ResourceModel\Step\Collection as StepCollection;
use Psr\Log\LoggerInterface;

/**
 * Class Data
 * @package Mageplaza\OrderAttributes\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH  = 'mporderattributes';
    const TEMPLATE_MEDIA_PATH = 'mageplaza/order_attributes';

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CarrierConfig
     */
    protected $carrierConfig;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var array
     */
    protected $optionsInvalid = [];

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var StepFactory
     */
    public $stepFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigInterface;

    /**
     * @var ThemeProviderInterface
     */
    protected $themeProvider;

    /**
     * @var WriteInterface
     */
    protected $newDirectory;

    /**
     * @var Dir
     */
    protected $directory;

    /**
     * @var File
     */
    protected $fileDriver;

    /**
     * @var Resolver
     */
    protected $_resolver;

    /**
     * @var StepCollection
     */
    protected $_stepCollection;

    /**
     * @var Cart
     */
    protected $cart;
    /**
     * @var Registry
     */
    public $registry;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param CarrierConfig $carrierConfig
     * @param CollectionFactory $collectionFactory
     * @param Repository $repository
     * @param Json $json
     * @param OrderFactory $orderFactory
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param ThemeProviderInterface $themeProvider
     * @param Dir $directory
     * @param File $fileDriver
     * @param Filesystem $file
     * @param Resolver $resolver
     * @param StepCollection $stepCollection
     * @param Cart $cart
     * @param Registry $registry
     * @param StepFactory $stepFactory
     *
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        CarrierConfig $carrierConfig,
        CollectionFactory $collectionFactory,
        Repository $repository,
        Json $json,
        OrderFactory $orderFactory,
        ScopeConfigInterface $scopeConfigInterface,
        ThemeProviderInterface $themeProvider,
        Dir $directory,
        File $fileDriver,
        Filesystem $file,
        Resolver $resolver,
        StepCollection $stepCollection,
        Cart $cart,
        Registry $registry,
        StepFactory $stepFactory
    ) {
        $this->customerSession      = $customerSession;
        $this->carrierConfig        = $carrierConfig;
        $this->collectionFactory    = $collectionFactory;
        $this->repository           = $repository;
        $this->json                 = $json;
        $this->orderFactory         = $orderFactory;
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->themeProvider        = $themeProvider;
        $this->directory            = $directory;
        $this->fileDriver           = $fileDriver;
        $this->newDirectory         = $file->getDirectoryWrite(DirectoryList::PUB);
        $this->_resolver            = $resolver;
        $this->_stepCollection      = $stepCollection;
        $this->cart                 = $cart;
        $this->registry             = $registry;
        $this->stepFactory          = $stepFactory;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param array $value
     *
     * @return bool|false|string
     */
    public function jsonEncodeData($value)
    {
        try {
            return $this->json->serialize($value);
        } catch (Exception $e) {
            return '{}';
        }
    }

    /**
     * @param string $value
     *
     * @return array
     */
    public function jsonDecodeData($value)
    {
        try {
            return $this->json->unserialize($value);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @param null $store
     *
     * @return bool
     */
    public function isDisabled($store = null)
    {
        return !$this->isEnabled($store);
    }

    /**
     * @return array
     */
    public function getInputType()
    {
        $inputTypes = [
            'text'               => [
                'label'         => __('Text Field'),
                'backend_type'  => 'varchar',
                'field_type'    => 'text',
                'default_value' => 'text',
                'component'     => 'Magento_Ui/js/form/element/abstract',
                'elementTmpl'   => 'ui/form/element/input'
            ],
            'textarea'           => [
                'label'         => __('Text Area'),
                'backend_type'  => 'text',
                'field_type'    => 'textarea',
                'default_value' => 'textarea',
                'component'     => 'Magento_Ui/js/form/element/textarea',
                'elementTmpl'   => 'ui/form/element/textarea'
            ],
            'date'               => [
                'label'         => __('Date'),
                'backend_type'  => 'datetime',
                'field_type'    => 'date',
                'default_value' => 'date',
                'component'     => 'Mageplaza_OrderAttributes/js/form/element/date',
                'elementTmpl'   => 'ui/form/element/date'
            ],
            'datetime'           => [
                'label'         => __('Date & Time'),
                'backend_type'  => 'datetime',
                'field_type'    => 'datetime',
                'default_value' => 'datetime',
                'component'     => 'Mageplaza_OrderAttributes/js/form/element/datetime',
                'elementTmpl'   => 'ui/form/element/date'
            ],
            'time'               => [
                'label'         => __('Time'),
                'backend_type'  => 'datetime',
                'field_type'    => 'time',
                'default_value' => 'time',
                'component'     => 'Mageplaza_OrderAttributes/js/form/element/time',
                'elementTmpl'   => 'ui/form/element/date'
            ],
            'boolean'            => [
                'label'         => __('Yes/No'),
                'backend_type'  => 'int',
                'field_type'    => 'select',
                'default_value' => 'yesno',
                'component'     => 'Mageplaza_OrderAttributes/js/form/element/select',
                'elementTmpl'   => 'ui/form/element/select',
            ],
            'select'             => [
                'label'         => __('Dropdown'),
                'backend_type'  => 'varchar',
                'field_type'    => 'select',
                'default_value' => false,
                'component'     => 'Mageplaza_OrderAttributes/js/form/element/select',
                'elementTmpl'   => 'ui/form/element/select'
            ],
            'multiselect'        => [
                'label'         => __('Multiple-Select'),
                'backend_type'  => 'varchar',
                'field_type'    => 'multiselect',
                'default_value' => false,
                'component'     => 'Magento_Ui/js/form/element/multiselect',
                'elementTmpl'   => 'ui/form/element/multiselect'
            ],
            'select_visual'      => [
                'label'         => __('Radio/Single-Select With Image'),
                'backend_type'  => 'varchar',
                'field_type'    => 'select',
                'default_value' => false,
                'component'     => 'Mageplaza_OrderAttributes/js/form/element/select',
                'elementTmpl'   => 'Mageplaza_OrderAttributes/form/element/radio-visual',
            ],
            'multiselect_visual' => [
                'label'         => __('Checkbox/Multiple-Select With Image'),
                'backend_type'  => 'varchar',
                'field_type'    => 'multiselect',
                'default_value' => false,
                'component'     => 'Mageplaza_OrderAttributes/js/form/element/checkboxes',
                'elementTmpl'   => 'Mageplaza_OrderAttributes/form/element/checkbox-visual',
            ],
            'image'              => [
                'label'         => __('Media Image'),
                'backend_type'  => 'text',
                'field_type'    => 'file',
                'default_value' => false,
                'component'     => 'Mageplaza_OrderAttributes/js/form/element/file-uploader',
                'elementTmpl'   => 'ui/form/element/uploader/uploader',
            ],
            'file'               => [
                'label'         => __('Single File Attachment'),
                'backend_type'  => 'text',
                'field_type'    => 'file',
                'default_value' => false,
                'component'     => 'Mageplaza_OrderAttributes/js/form/element/file-uploader',
                'elementTmpl'   => 'ui/form/element/uploader/uploader',
            ],
            'textarea_visual'    => [
                'label'         => __('Content'),
                'backend_type'  => 'text',
                'field_type'    => 'content',
                'default_value' => 'content',
                'component'     => 'Mageplaza_OrderAttributes/js/form/element/textarea',
                'elementTmpl'   => 'ui/form/element/textarea',
            ],
            'cms_block'          => [
                'label'         => __('Static Block'),
                'backend_type'  => 'text',
                'field_type'    => 'hidden',
                'default_value' => 'cms_block',
                'component'     => 'Magento_Ui/js/form/element/textarea',
                'elementTmpl'   => 'Mageplaza_OrderAttributes/form/element/cms-block'
            ],
        ];

        return $inputTypes;
    }

    /**
     * @param string $inputType
     *
     * @return string|false
     */
    public function getDefaultValueByInput($inputType)
    {
        $inputTypes = $this->getInputType();
        if (isset($inputTypes[$inputType])) {
            $value = $inputTypes[$inputType]['default_value'];
            if ($value) {
                return 'default_value_' . $value;
            }
        }

        return false;
    }

    /**
     * @param string $inputType
     *
     * @return string|null
     */
    public function getBackendTypeByInputType($inputType)
    {
        $inputTypes = $this->getInputType();
        if (!empty($inputTypes[$inputType]['backend_type'])) {
            return $inputTypes[$inputType]['backend_type'];
        }

        return null;
    }

    /**
     * @param string $inputType
     *
     * @return string|null
     */
    public function getFieldTypeByInputType($inputType)
    {
        $inputTypes = $this->getInputType();
        if (!empty($inputTypes[$inputType]['field_type'])) {
            return $inputTypes[$inputType]['field_type'];
        }

        return null;
    }

    /**
     * @param string $inputType
     *
     * @return string|false
     */
    public function getComponentByInputType($inputType)
    {
        $inputTypes = $this->getInputType();
        if (!empty($inputTypes[$inputType]['component'])) {
            return $inputTypes[$inputType]['component'];
        }

        return null;
    }

    /**
     * @param string $inputType
     *
     * @return string|false
     */
    public function getElementTmplByInputType($inputType)
    {
        $inputTypes = $this->getInputType();
        if (!empty($inputTypes[$inputType]['elementTmpl'])) {
            return $inputTypes[$inputType]['elementTmpl'];
        }

        return null;
    }

    /**
     * @param null $storeId
     * @param null $groupId
     *
     * @return Attribute[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getFilteredAttributes($storeId = null, $groupId = null)
    {
        return $this->getOrderAttributesCollection($storeId, $groupId);
    }

    /**
     * @param null|string|int $storeId
     * @param null|string|int $groupId
     * @param array $filters
     * @param bool $isCheckVisible
     *
     * @return array|Collection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getOrderAttributesCollection($storeId, $groupId, $isCheckVisible = true, $filters = [])
    {
        $result = [];

        $attributes = $this->collectionFactory->create();
        if ($filters) {
            foreach ($filters as $field => $cond) {
                $attributes->addFieldToFilter($field, $cond);
            }
        }

        $items = $attributes->getItems();
        if (!$isCheckVisible) {
            return $items;
        }

        foreach ($items as $attribute) {
            /**
             * @var Attribute $attribute
             */
            if ($this->isVisible($attribute, $storeId, $groupId)) {
                $result[] = $attribute;
            }
        }

        return $result;
    }

    /**
     * @param Attribute $attribute
     * @param string|null $storeId
     * @param string|null $groupId
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isVisible($attribute, $storeId, $groupId)
    {
        $storeId = $storeId ?: $this->getScopeId();
        $groupId = $groupId ?: $this->getGroupId();
        $stores  = $attribute->getStoreId() ?: 0;
        $stores  = explode(',', $stores);
        $groups  = $attribute->getCustomerGroup() ?: 0;
        $groups  = explode(',', $groups);

        $isVisibleStore = in_array(0, $stores) || in_array($storeId, $stores);
        $isVisibleGroup = (!$groupId && $this->isAdmin()) ?: in_array($groupId, $groups);

        return $isVisibleStore && $isVisibleGroup && $attribute->getPosition();
    }

    /**
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getScopeId()
    {
        $scopeStore = $this->_request->getParam(ScopeInterface::SCOPE_STORE);
        $scopeId    = $scopeStore ?: $this->storeManager->getStore()->getId();

        if ($website = $this->_request->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            $defaultStore = $this->storeManager->getWebsite($website)->getDefaultStore();
            if ($defaultStore) {
                $scopeId = $defaultStore->getId();
            }
        }

        return $scopeId;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->customerSession->getCustomer()->getGroupId();
        }

        return 0;
    }

    /**
     * @param string $attrCode
     * @param int|string $value
     *
     * @return bool
     * @throws InputException
     */
    public function validateBoolean($attrCode, $value)
    {
        if (!in_array($value, ['0', '1', 0, 1, true, false], true)) {
            throw new InputException(__('%1 invalid', $attrCode));
        }

        return true;
    }

    /**
     * @param string $date
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isValidDate($date)
    {
        if (!date_create($date)) {
            throw new InputException(__('Invalid date'));
        }

        return true;
    }

    /**
     * @param string $fileUpload
     * @param string $fileDb
     * @param string $attrCode
     *
     * @return bool
     * @throws InputException
     */
    public function validateFile($fileUpload, $fileDb, $attrCode)
    {
        $fileUploadDecode = $this->jsonDecodeData($fileUpload);
        $fileDbDecode     = $this->jsonDecodeData($fileDb);
        $fields           = ['file', 'name', 'size', 'url'];

        foreach ($fields as $field) {
            $fieldUpload = isset($fileUploadDecode[$field]) ? $fileUploadDecode[$field] : '';
            $fieldDb     = isset($fileDbDecode[$field]) ? $fileDbDecode[$field] : '';
            if ($field === 'size') {
                $fieldUpload = (int) $fieldUpload;
                $fieldDb     = (int) $fieldDb;
            }
            if (!$fieldDb || !$fieldUpload || ($fieldDb !== $fieldUpload)) {
                throw new InputException(
                    __('Something went wrong while uploading file (attribute %1)', $attrCode)
                );
            }
        }

        return true;
    }

    /**
     * @param string|int $storeId
     * @param array $attributeSubmit
     *
     * @return array
     */
    public function prepareAttributes($storeId, $attributeSubmit)
    {
        $attributes = $this->collectionFactory->create();
        $result     = [];
        $storeId    = (int) $storeId;
        foreach ($attributes->getItems() as $attribute) {
            $attrCode      = $attribute->getAttributeCode();
            $frontendInput = $attribute->getFrontendInput();

            if (!isset($attributeSubmit[$attrCode])) {
                continue;
            }

            if (!$attributeSubmit[$attrCode] && $attributeSubmit[$attrCode] !== '0') {
                $result[$attrCode] = '';
                continue;
            }

            $result[$attrCode . '_label'] = $this->prepareLabel($attribute, $storeId);

            $value             = $attributeSubmit[$attrCode];
            $result[$attrCode] = $value;
            switch ($frontendInput) {
                case 'boolean':
                    $result[$attrCode . '_option'] = $this->prepareBoolValue($value);
                    break;
                case 'select':
                case 'multiselect':
                case 'select_visual':
                case 'multiselect_visual':
                    $options = $this->prepareOptionValue($attribute->getOptions(), $value, $storeId);

                    $result[$attrCode . '_option'] = $options;
                    break;
                case 'image':
                case 'file':
                    $result[$attrCode . '_name'] = $this->prepareFileName($value);
                    $result[$attrCode . '_url']  = $this->prepareFileValue($frontendInput, $value);
                    break;
            }
        }

        return $result;
    }

    /**
     * @param $attribute
     * @param $storeId
     *
     * @return mixed
     */
    public function prepareLabel($attribute, $storeId)
    {
        $labels = $this->jsonDecodeData($attribute->getLabels());

        return !empty($labels[$storeId]) ? $labels[$storeId] : $attribute->getFrontendLabel();
    }

    /**
     * @param $quote
     * @param $attributeSubmit
     * @param $quoteAttribute
     *
     * @return array
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function validateAttributes($quote, $attributeSubmit, $quoteAttribute)
    {
        $attributes = $this->collectionFactory->create();
        $result     = [];
        $storeId    = $quote->getStoreId() ?: 0;
        foreach ($attributes->getItems() as $attribute) {
            if ($this->isVisible($attribute, $storeId, $quote->getCustomerGroupId())) {
                $attrCode      = $attribute->getAttributeCode();
                $frontendInput = $attribute->getFrontendInput();

                if (!isset($attributeSubmit[$attrCode])) {
                    continue;
                }

                if ($attributeSubmit[$attrCode] === '' && $attribute->getIsRequired()
                    && !$quote->isVirtual() && $this->isVisiableInStep($quote, $attribute)) {
                    throw new InputException(__('%1 is required', $attribute->getFrontendLabel()));
                }

                if (!$attributeSubmit[$attrCode] && $attributeSubmit[$attrCode] !== '0') {
                    $result[$attrCode] = '';
                    continue;
                }

                $result[$attrCode . '_label'] = $this->prepareLabel($attribute, $storeId);

                $value             = $attributeSubmit[$attrCode];
                $value             = is_array($value) ? implode(',', $value) : $value;
                $result[$attrCode] = $value;
                switch ($frontendInput) {
                    case 'boolean':
                        $this->validateBoolean($attrCode, $value);
                        $result[$attrCode . '_option'] = $this->prepareBoolValue($value);
                        break;
                    case 'select':
                    case 'multiselect':
                    case 'select_visual':
                    case 'multiselect_visual':
                        $options = $this->prepareOptionValue($attribute->getOptions(), $value, $storeId);
                        if ($this->getOptionsInvalid()) {
                            throw new InputException(
                                __('Invalid options %1. Details: %1 ', implode($this->getOptionsInvalid()), $attrCode)
                            );
                        }
                        $result[$attrCode . '_option'] = $options;

                        break;
                    case 'image':
                    case 'file':
                        $this->validateFile($value, $quoteAttribute->getData($attrCode), $attrCode);
                        $result[$attrCode . '_name'] = $this->prepareFileName($value);
                        $result[$attrCode . '_url']  = $this->prepareFileValue($frontendInput, $value);
                        break;
                }
            }
        }

        if ($result) {
            $quoteAttribute->saveAttributeData($quote->getId(), $result);
        }

        return $result;
    }

    /**
     * Check the current page is OSC
     *
     * @return bool
     */
    public function isOscPage()
    {
        $moduleEnable = $this->isModuleOutputEnabled('Mageplaza_Osc');
        $isOscModule  = ($this->_request->getRouteName() === 'onestepcheckout');

        return $moduleEnable && $isOscModule;
    }

    /**
     * Get all shipping methods
     *
     * @return array
     */
    public function getShippingMethods()
    {
        $activeCarriers = $this->carrierConfig->getActiveCarriers();
        $methods        = [];

        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            if ($carrierCode === 'temando') {
                continue;
            }
            $options       = [];
            $carrierTitle  = '';
            $allowedMethod = $carrierModel->getAllowedMethods();
            if (is_array($allowedMethod)) {
                foreach ($allowedMethod as $methodCode => $method) {
                    $code      = $carrierCode . '_' . $methodCode;
                    $options[] = [
                        'value' => $code,
                        'label' => $method
                    ];
                }

                $carrierTitle = $carrierModel->getConfigData('title');
            }

            $methods[] = [
                'value' => $options,
                'label' => $carrierTitle
            ];
        }

        return $methods;
    }

    /**
     * @param AbstractModel $object
     * @param string $type
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function applyFilter(AbstractModel $object, $type = 'output')
    {
        $attributes = $this->getOrderAttributesCollection(
            null,
            null,
            false,
            [
                'input_filter' => ['neq' => 'NULL']
            ]
        );

        foreach ($attributes as $attribute) {
            $value = $object->getData($attribute->getAttributeCode());
            if ($value) {
                $filter = $this->getFilterClass($attribute->getInputFilter());
                if ($type === 'input') {
                    $value = $filter->inputFilter($value);
                } else {
                    $value = $filter->outputFilter($value);
                }

                $object->setData($attribute->getAttributeCode(), $value);
            }
        }
    }

    /**
     * Return Input/Output Filter Class
     *
     * @param $filterCode
     *
     * @return FilterInterface
     */
    protected function getFilterClass($filterCode)
    {
        $filterClass = 'Magento\Framework\Data\Form\Filter\\' . ucfirst($filterCode);

        return new $filterClass();
    }

    /**
     * @return string
     */
    public function getBaseTmpMediaPath()
    {
        return self::TEMPLATE_MEDIA_PATH . '/tmp';
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseTmpMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $this->getBaseTmpMediaPath();
    }

    /**
     * @param string $file
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getTmpMediaUrl($file)
    {
        return $this->getBaseTmpMediaUrl() . '/' . $this->_prepareFile($file);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function _prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    /**
     * Move file from temporary directory into base directory
     *
     * @param $file
     *
     * @return string
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function moveTemporaryFile($file)
    {
        /** @var Filesystem $fileSystem */
        $fileSystem     = $this->getObject(Filesystem::class);
        $directoryRead  = $fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $directoryWrite = $fileSystem->getDirectoryWrite(DirectoryList::MEDIA);

        $path    = $this->getBaseTmpMediaPath() . $file['file'];
        $newName = Uploader::getNewFileName($directoryRead->getAbsolutePath($path));
        $newPath = self::TEMPLATE_MEDIA_PATH . Uploader::getDispretionPath($newName);

        if (!$directoryWrite->create($newPath)) {
            throw new LocalizedException(
                __('Unable to create directory %1.', $newPath)
            );
        }

        if (!$directoryWrite->isWritable($newPath)) {
            throw new LocalizedException(
                __('Destination folder is not writable or does not exists.')
            );
        }

        $directoryWrite->renameFile($path, $newPath . '/' . $newName);

        return Uploader::getDispretionPath($newName) . '/' . $newName;
    }

    /**
     * @param $value
     *
     * @return Phrase
     */
    public function prepareBoolValue($value)
    {
        return $value ? __('Yes') : __('No');
    }

    /**
     * @param $options
     * @param $values
     * @param $storeId
     *
     * @return string
     */
    public function prepareOptionValue($options, $values, $storeId)
    {
        $this->optionsInvalid = [];

        $options = $this->jsonDecodeData($options);
        $result  = [];

        switch (true) {
            case isset($options['option']['value']):
                $options = $options['option']['value'];
                break;
            case isset($options['optionvisual']['value']):
                $options = $options['optionvisual']['value'];
                break;
        }

        foreach (explode(',', $values) as $value) {
            if ($value && isset($options[$value])) {
                $option   = $options[$value];
                $result[] = !empty($option[$storeId]) ? $option[$storeId] : $option[0];
            } else {
                $this->optionsInvalid[] = $value;
            }
        }

        return implode(', ', $result);
    }

    /**
     * @return array
     */
    public function getOptionsInvalid()
    {
        return $this->optionsInvalid;
    }

    /**
     * @param string $value
     *
     * @return string|null
     */
    public function prepareDateValue($value)
    {
        return $value ? date($this->getConfigDateFormat(), strtotime($value)) : null;
    }

    /**
     * @param string $value
     *
     * @return string|null
     */
    public function prepareDateTimeValue($value)
    {
        return $value ? date($this->getConfigDateTimeFormat(), strtotime($value)) : null;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function prepareFileName($value)
    {
        return substr($value, strrpos($value, '/') + 1);
    }

    /**
     * @param $frontendInput
     * @param $value
     *
     * @return string
     */
    public function prepareFileValue($frontendInput, $value)
    {
        $param = '/' . $frontendInput . '/' . $this->urlEncoder->encode($value);

        return $this->_urlBuilder->getUrl('mporderattributes/viewfile/index' . $param);
    }

    /**
     * @return bool|string
     */
    public function getTinymceConfig()
    {
        if ($this->versionCompare('2.3.0')) {
            $tinymce = 'tinymce4';
            if ($this->versionCompare('2.4.4')) {
                $tinymce = 'tinymce';
            }
            $config = [
                $tinymce => [
                    'toolbar'     => 'formatselect | bold italic underline | alignleft aligncenter alignright | '
                        . 'bullist numlist | link table charmap',
                    'plugins'     => implode(
                        ' ',
                        [
                            'advlist',
                            'autolink',
                            'lists',
                            'link',
                            'charmap',
                            'media',
                            'noneditable',
                            'table',
                            'contextmenu',
                            'paste',
                            'code',
                            'help',
                            'table'
                        ]
                    ),
                    'content_css' => $this->repository->getUrl('mage/adminhtml/wysiwyg/tiny_mce/themes/ui.css')
                ]
            ];

            return $this->jsonEncodeData($config);
        }

        return false;
    }

    /**
     * @param Order|OrderInterface $order
     */
    public function addDataToOrder($order)
    {
        if ($this->isEnabled($order->getStoreId())) {
            $orderAttributeModel = $this->orderFactory->create();
            $orderAttributeModel->load($order->getId());
            if ($orderAttributeModel->getId()) {
                $result = $this->prepareAttributes($order->getStoreId(), $orderAttributeModel->getData());
                $order->addData($result);
            }
        }
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return array|mixed
     */
    public function getCheckoutConfig($code, $store = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . '/checkout_configuration' . $code, $store);
    }

    /**
     * @param null $store
     *
     * @return mixed
     */
    public function getDateFormat($store = null)
    {
        return $this->getCheckoutConfig('date_format', $store);
    }

    /**
     * @param null $store
     *
     * @return mixed
     */
    public function getTimeFormat($store = null)
    {
        return $this->getCheckoutConfig('time_format', $store);
    }

    /**
     * @return int|string
     */
    public function getConfigDateFormat()
    {
        return array_search($this->getDateFormat(), $this->getDateFormatConfig()) ?: 'M d, Y';
    }

    /**
     * @return int|string
     */
    public function getConfigDateTimeFormat()
    {
        $format = 'M d, Y H:i:s';

        $dateFormat = array_search($this->getDateFormat(), $this->getDateFormatConfig());
        $timeFormat = array_search($this->getTimeFormat(), $this->getTimeFormatConfig());

        if ($dateFormat) {
            $format = $dateFormat;
            if ($timeFormat) {
                $format .= ' ' . $timeFormat;
            }
        }

        return $format;
    }

    /**
     * @return array
     */
    public function getDateFormatConfig()
    {
        return [
            'Y-m-d' => 'yy-mm-dd',
            'm/d/Y' => 'mm/dd/yy',
            'd/m/Y' => 'dd/mm/yy',
            'j/n/y' => 'd/m/y',
            'j/m/Y' => 'd/m/yy',
            'd.m.Y' => 'dd.mm.yy',
            'd.m.y' => 'dd.mm.y',
            'j.n.y' => 'd.m.y',
            'j.n.Y' => 'd.m.yy',
            'd-m-y' => 'dd-mm-y',
            'Y.m.d' => 'yy.mm.dd',
            'd-m-Y' => 'dd-mm-yy',
            'Y/m/d' => 'yy/mm/dd',
            'y/m/d' => 'y/mm/dd',
            'd/m/y' => 'dd/mm/y',
            'm/d/y' => 'mm/dd/y',
            'd/m Y' => 'dd/mm yy',
            'Y m d' => 'yy mm dd'
        ];
    }

    /**
     * @return array
     */
    public function getTimeFormatConfig()
    {
        return [
            'H:i:s'   => 'HH:mm:ss',
            'h:i:s A' => 'hh:mm:ss TT',
            'H:i'     => 'HH:mm',
            'h:i A'   => 'hh:mm TT'
        ];
    }

    /**
     * @return array|mixed
     */
    public function getTimezone()
    {
        return $this->getConfigValue('general/locale/timezone');
    }

    /**
     * @return StepCollection
     */
    public function getStepCollection()
    {
        if (!$this->registry->registry('step_collection_checkout')) {
            $steps = $this->stepFactory->create()->getCollection();
            $steps->addFieldToFilter(
                'position',
                ['in' => [PositionStep::BEFORE_SHIPPING, PositionStep::AFTER_SHIPPING]]
            )->addFieldToFilter('status', Status::ENABLE);
            $steps->load();
            $this->registry->register('step_collection_checkout', $steps);
        }

        return $this->registry->registry('step_collection_checkout');
    }

    /**
     * @param Address $address
     *
     * @return StepCollection|null
     */
    public function getStepCollectionFiltered($address)
    {
        if (!$this->registry->registry('step_collection_checkout_filtered')) {
            $steps = $this->_stepCollection->setOrder('sort_order', 'ASC');
            $steps->addFieldToFilter(
                'position',
                ['in' => [PositionStep::BEFORE_SHIPPING, PositionStep::AFTER_SHIPPING]]
            )->addFieldToFilter('status', Status::ENABLE);
            $steps->load();
            if (!$address->getTotalQty()) {
                $address->setTotalQty($address->getQuote()->getItemsQty());
            }
            /** @var Step $step */
            foreach ($steps as $key => $step) {
                if (!$this->isInStoreAndCustomerGroup($step, $address->getQuote())
                    || !$step->isMatchCondition($address)) {
                    $steps->removeItemByKey($key);
                }
            }
            $this->registry->register('step_collection_checkout_filtered', $steps);
        }

        return $this->registry->registry('step_collection_checkout_filtered');
    }

    /**
     * @param Address|\Magento\Quote\Api\Data\AddressInterface $address
     *
     * @return array
     */
    public function getStepCodesFiltered($address)
    {
        $stepCodes = [''];
        $steps = $this->getStepCollectionFiltered($address);
        foreach ($steps as $step) {
            $stepCodes[] = $step->getCode();
        }

        return $stepCodes;
    }

    /**
     * @param Step $step
     * @param Quote $quote
     *
     * @return bool
     */

    private function isInStoreAndCustomerGroup($step, $quote)
    {
        $stepCustomer = $step->getCustomerGroup();
        $stepStoreId  = $step->getStoreId();
        $stepCustomer = explode(',', $stepCustomer);
        $stepStoreId  = explode(',', $stepStoreId);
        if (in_array(0, $stepCustomer) && in_array(0, $stepStoreId)) {
            return true;
        }
        if (!in_array($quote->getCustomerGroupId(), $stepCustomer) || !in_array($quote->getStoreId(), $stepStoreId)) {
            return false;
        }

        return true;
    }

    /**
     * @param null $storeId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getThemeCode($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $themeId = $this->scopeConfig->getValue(
            DesignInterface::XML_PATH_THEME_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $theme = $this->themeProvider->getThemeById($themeId);

        return $theme->getCode();
    }

    /**
     * @param Step $step
     * @param null $localCode
     * @param null $area
     *
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function createJsFileStep($step, $localCode = null, $area = null)
    {
        if ($localCode === null) {
            $localCode = $this->getLocaleCode($this->storeManager->getStore()->getId());
        }
        $fileNameOrigin = $this->directory->getDir('Mageplaza_OrderAttributes')
            . '/view/frontend/web/js/view/step/mp-custom-step.js';
        if ($this->checkFileExists($fileNameOrigin)) {
            $stepCode = $step->getCode();
            $pathStep = <<<STRING
                static/frontend/{$this->getThemeCode($this->storeManager->getStore()->getId())}/{$localCode}/Mageplaza_OrderAttributes/js/view/step
                STRING;
            $this->createDirectory($pathStep);
            $pathStep     .= '/mp-custom-step';
            $fileNameStep = $this->newDirectory->getAbsolutePath() . $pathStep . '-' . $stepCode . '.js';

            if ($area !== 'back_end' && !$this->checkFileExists($fileNameStep)) {
                $content = $this->fileDriver->fileGetContents($fileNameOrigin);
                $this->writeFileForStep($content, $step, $fileNameStep);
            } else {
                if ($area === 'back_end') {
                    $content = $this->fileDriver->fileGetContents($fileNameOrigin);
                    $this->writeFileForStep($content, $step, $fileNameStep);
                }
            }
        }
    }

    /**
     * @param string $content
     * @param Step $step
     * @param string $fileNameStep
     *
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function writeFileForStep($content, $step, $fileNameStep)
    {
        $content = str_replace('step_code', $step->getData('code'), $content);
        $content = str_replace('step_title', $step->getData('name'), $content);
        $content = str_replace('icon_type', $step->getData('icon_type'), $content);
        $content = str_replace('icon_class', $step->getIconClass(), $content);
        $content = str_replace('sort_order', $step->getSortOrder(), $content);
        if ($step->getIconCustom()) {
            $content = str_replace(
                'icon_img',
                $this->getMediaUrl() . $step->getIconCustom(),
                $content
            );
        } else {
            $content = str_replace(
                'icon_img',
                '',
                $content
            );
        }
        $this->fileDriver->filePutContents($fileNameStep, $content);
    }

    /**
     * @param $path
     *
     * @return bool
     * @throws FileSystemException
     */
    public function createDirectory($path)
    {
        try {
            $newDirectory = $this->newDirectory->create($path);
        } catch (FileSystemException $e) {
            throw new FileSystemException(
                __('We can\'t create directory "%1"', $path)
            );
        }

        return $newDirectory;
    }

    /**
     * @param $fileName
     *
     * @return bool
     * @throws FileSystemException
     */
    public function checkFileExists($fileName)
    {
        if ($this->fileDriver->isExists($fileName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->cart->getQuote();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    public function getStores()
    {
        return $this->storeManager->getStores();
    }

    /**
     * @param $scopeId
     *
     * @return mixed|string|null
     */
    public function getLocaleCode($scopeId)
    {
        return $this->_resolver->emulate($scopeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function isDisplayAttributesInShipmentPdf($storeId = null)
    {
        return $this->getConfigValue(
            static::CONFIG_MODULE_PATH . '/m2_pdf_docs/display_attribute_into_shipment',
            $storeId
        );
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function isDisplayAttributesInvoicePdf($storeId = null)
    {
        return $this->getConfigValue(
            static::CONFIG_MODULE_PATH . '/m2_pdf_docs/display_attribute_into_invoice',
            $storeId
        );
    }

    /**
     * @param Quote $quote
     * @param $attribute
     *
     * @return bool
     */
    protected function isVisiableInStep($quote, $attribute)
    {
        $address = $quote->getShippingAddress();
        if (!in_array($attribute->getPosition(), $this->getStepCodesFiltered($address))) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getFullActionName()
    {
        return $this->_getRequest()->getFullActionName();
    }
}
