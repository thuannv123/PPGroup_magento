<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Feeds\Parser\Operator;

use Firebear\PlatformFeeds\Feeds\Parser\Abstracts\AbstractParser;
use Firebear\PlatformFeeds\Feeds\Parser\Variable\Product as ProductVariable;

/**
 * @method ForProductOperator setForGalleryOperator(ForGalleryOperator $forGalleryOperator)
 * @method ForGalleryOperator getForGalleryOperator()
 * @method ForProductOperator setTemplateParts(array $parts)
 * @method ForProductOperator setProductVariable(ProductVariable $productVariable)
 * @method ProductVariable getProductVariable()
 */
class ForProductOperator extends AbstractParser
{
    /**
     * @var string
     */
    const FOR_PATTERN = '/{%[\s]*for product in set[\s]*%}(.*?){%[\s]*endforProduct[\s]*%}/si';

    /**
     * @var int
     */
    const INDEX_PART_WITH_CODE = 0;

    /**
     * @var int
     */
    const INDEX_PART_WITHOUT_CODE = 1;

    /**
     * @var string[][]
     */
    protected $translations = [];

    /**
     * AbstractTemplateParser constructor.
     *
     * @param ForGalleryOperator $forGalleryOperator
     * @param ProductVariable $productVariable
     * @param array $data
     */
    public function __construct(
        ForGalleryOperator $forGalleryOperator,
        ProductVariable $productVariable,
        array $data = []
    ) {
        parent::__construct($data);

        $this->setProductVariable($productVariable);
        $this->setForGalleryOperator($forGalleryOperator);
    }

    /**
     * @inheritdoc
     */
    public function translate(array $data)
    {
        $parts = $this->getTemplateParts(self::INDEX_PART_WITHOUT_CODE);
        if (empty($parts)) {
            return $this;
        }

        $this->setRowData($data);

        foreach ($parts as $key => $value) {
            $this->getProductVariable()->setTemplate($value);
            $value = $this->getProductVariable()->translate($data);
            $value = $this->applyForGalleryOperator($value);

            $this->translations[$key][] = $value;
        }

        return $this;
    }

    /**
     * Fetch for entities
     */
    public function fetchEntries()
    {
        $template = $this->getTemplate();
        if (empty($template)) {
            return;
        }

        preg_match_all(self::FOR_PATTERN, $this->getTemplate(), $matches);
        $this->setTemplateParts($matches);
    }

    /**
     * @inheritdoc
     */
    public function getResult()
    {
        $template = $this->getTemplate();
        $parts = $this->getTemplateParts(self::INDEX_PART_WITH_CODE);
        if (empty($parts)) {
            return $template;
        }

        foreach ($parts as $key => $part) {
            $replacement = implode('', $this->translations[$key]);
            $template = str_replace($part, $replacement, $template);
        }

        return $template;
    }

    /**
     * Get template parts
     *
     * @param int $type
     * @return array
     */
    public function getTemplateParts($type)
    {
        $parts = [];
        $templateParts = $this->getData('template_parts');
        if (!empty($templateParts[$type])) {
            $parts = $templateParts[$type];
        }

        return $parts;
    }

    /**
     * Apply for gallery operator
     *
     * @param string $template
     * @return string
     */
    protected function applyForGalleryOperator($template)
    {
        $forGalleryOperator = $this->getForGalleryOperator();
        $forGalleryOperator->setTemplate($template);

        return $forGalleryOperator->translate($this->getRowData());
    }
}
