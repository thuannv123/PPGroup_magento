<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package FAQ and Product Questions for Magento 2
 */

namespace Amasty\Faq\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface QuestionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get FAQ questions list
     *
     * @return \Amasty\Faq\Api\Data\QuestionInterface[]
     */
    public function getItems();

    /**
     * Set FAQ questions list
     *
     * @param \Amasty\Faq\Api\Data\QuestionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
