<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Source;

use Amasty\Blog\Api\TagRepositoryInterface;
use Magento\Framework\Data\OptionSourceInterface;

class Tags implements OptionSourceInterface
{
    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    public function __construct(
        TagRepositoryInterface $tagRepository
    ) {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $tags = [];
        $collection = $this->tagRepository->getAllTags();
        foreach ($collection as $tag) {
            $tags[] = [
                'value' => $tag->getTagId(),
                'label' => $tag->getName()
            ];
        }

        return $tags;
    }
}
