<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Feeds;

use Magento\Framework\DataObject;
use Firebear\PlatformFeeds\Feeds\Parser\Operator\ForProductOperator;
use Firebear\PlatformFeeds\Feeds\Parser\Variable\Template;
use Firebear\PlatformFeeds\Helper\Data as FeedsDataHelper;

/**
 * @method Processor setTemplate(string $template)
 * @method string getTemplate()
 * @method ForProductOperator getForParser()
 * @method Processor setForParser(ForProductOperator $forParser)
 * @method Template getTemplateParser()
 * @method Processor setHelper(FeedsDataHelper $helper)
 * @method FeedsDataHelper getHelper()
 */
class Processor extends DataObject
{
    /**
     * Parser constructor.
     *
     * @param ForProductOperator $forParser
     * @param Template $templateParser
     * @param FeedsDataHelper $feedsOptionsHelper
     * @param array $data
     */
    public function __construct(
        ForProductOperator $forParser,
        Template $templateParser,
        FeedsDataHelper $feedsOptionsHelper,
        array $data = []
    ) {
        parent::__construct($data);

        $this->setHelper($feedsOptionsHelper);
        $this->setForParser($forParser);
        $this->setTemplateParser($templateParser);
    }

    /**
     * Process row
     *
     * @param array $rowData
     */
    public function processRow($rowData)
    {
        if (!$this->getForParser()->getTemplate()) {
            $this->getForParser()->setTemplate($this->getTemplate());
            $this->getForParser()->fetchEntries();
        }

        $this->getForParser()->translate($rowData);
    }

    /**
     * Set template parser
     *
     * @param Template $templateParser
     * @return Processor
     */
    public function setTemplateParser(Template $templateParser)
    {
        $templateParser->setTemplate($this->getTemplate());
        return $this->setData('template_parser', $templateParser);
    }

    /**
     * Get result
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @see Processor::afterProcessing
     */
    public function getResult()
    {
        $template = $this->getForParser()->getResult();
        $this->getTemplateParser()->setTemplate($template);

        return $this->getTemplateParser()->translate($this->getHelper()->getTemplateData());
    }
}
