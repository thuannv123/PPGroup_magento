<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package FAQ and Product Questions for Magento 2
 */

namespace Amasty\Faq\Model\Import\Question;

use Amasty\Base\Model\Import\Mapping\Mapping as MappingBase;
use Amasty\Faq\Api\ImportExport\QuestionInterface;

class Mapping extends MappingBase implements \Amasty\Base\Model\Import\Mapping\MappingInterface
{
    /**
     * @var array
     */
    protected $mappings = [
        QuestionInterface::QUESTION_ID,
        QuestionInterface::QUESTION,
        QuestionInterface::URL_KEY,
        QuestionInterface::STORE_CODES,
        QuestionInterface::SHORT_ANSWER,
        QuestionInterface::ANSWER,
        QuestionInterface::STATUS,
        QuestionInterface::VISIBILITY,
        QuestionInterface::POSITION,
        QuestionInterface::META_TITLE,
        QuestionInterface::META_DESCRIPTION,
        QuestionInterface::NAME,
        QuestionInterface::EMAIL,
        QuestionInterface::CATEGORY_IDS,
        QuestionInterface::PRODUCT_SKUS,
        QuestionInterface::IS_SHOW_FULL_ANSWER
    ];

    /**
     * @var string
     */
    protected $masterAttributeCode = QuestionInterface::QUESTION;
}
