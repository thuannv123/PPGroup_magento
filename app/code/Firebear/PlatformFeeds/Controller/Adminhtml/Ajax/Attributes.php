<?php
/**
 * @copyright: Copyright © 2018 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Firebear\PlatformFeeds\Helper\Variable as FeedsVariableHelper;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception as WebApiException;

class Attributes extends Action
{
    /**
     * @var Collection
     */
    protected $attributeCollection;

    /**
     * @var FeedsVariableHelper
     */
    protected $feedsVariableHelper;

    /**
     * Attributes constructor.
     *
     * @param Action\Context $context
     * @param Collection $attributeCollection
     * @param FeedsVariableHelper $feedsVariableHelper
     */
    public function __construct(
        Action\Context $context,
        Collection $attributeCollection,
        FeedsVariableHelper $feedsVariableHelper
    ) {
        $this->attributeCollection = $attributeCollection;
        $this->feedsVariableHelper = $feedsVariableHelper;

        parent::__construct($context);
    }

    /**
     * @inheritdoc
     * @return Json
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var Json $result */
        $result = $this->resultFactory->create($this->resultFactory::TYPE_JSON);
        if (!$this->getRequest()->isAjax()) {
            $result->setHttpResponseCode(WebApiException::HTTP_BAD_REQUEST);
            return $result;
        }

        $options = $this->feedsVariableHelper->getVariables();
        return $result->setData($options);
    }
}
