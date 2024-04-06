<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Feeds\Parser\Variable;

use Firebear\PlatformFeeds\Feeds\Parser\Operator\IfOperator;
use Firebear\PlatformFeeds\Feeds\Parser\Abstracts\AbstractParser;
use Firebear\PlatformFeeds\Feeds\Parser\Mapper\Mapper;

/**
 * @method AbstractVariable setIfOperator(IfOperator $ifOperator)
 * @method IfOperator getIfOperator()
 * @method AbstractVariable setVariableNamespace(string $namespace)
 * @method Modifier getModifier()
 * @method AbstractVariable setModifier(Modifier $modifier)
 * @method Mapper getMapper()
 * @method AbstractVariable setMapper(Mapper $mapper)
 */
abstract class AbstractVariable extends AbstractParser
{
    /**
     * @var string
     */
    const VARIABLE_PATTERN = '/\{\{[\s]*%s\.([^}]+)\}\}/si';

    /**
     * @var string
     */
    const VARIABLE_NAME_PATTERN = '/%s.([a-z_0-9]+)/';

    /**
     * AbstractVariable constructor.
     *
     * @param IfOperator $ifOperator
     * @param Modifier $modifier
     * @param Mapper $mapper
     * @param array $data
     */
    public function __construct(
        IfOperator $ifOperator,
        Modifier $modifier,
        Mapper $mapper,
        array $data = []
    ) {
        parent::__construct($data);

        $this->setModifier($modifier);
        $this->setMapper($mapper);
        $this->setIfOperator($ifOperator);
    }

    /**
     * @inheritdoc
     */
    public function translate(array $data)
    {
        $this->setRowData($data);
        $this->applyIfOperator();

        return preg_replace_callback(
            $this->getPattern(self::VARIABLE_PATTERN),
            [&$this, "replaceCallback"],
            $this->getTemplate()
        );
    }

    /**
     * Replace callback
     *
     * @param array $matches
     * @return string
     * @see VariableParser::translate()
     */
    protected function replaceCallback($matches)
    {
        $rowData = $this->getRowData();
        preg_match($this->getPattern($this::VARIABLE_NAME_PATTERN), $matches[0], $attributeName);

        if (!empty($attributeName[1]) && isset($rowData[$attributeName[1]])) {
            $value = $rowData[$attributeName[1]];
            return $this->getProcessedVariable($matches[0], $attributeName[1], $value);
        }

        return $matches[0];
    }

    /**
     * Get processed value of variable
     *
     * @param string $pattern
     * @param string $name
     * @param string $value
     * @return string
     */
    protected function getProcessedVariable($pattern, $name, $value)
    {
        $oldValue = $value;
        $value = $this->getModifier()->modify($value, $pattern);
        $value = $this->getMapper()->getMapped($name, $value, $oldValue, $this->getModifier(), $pattern);

        return $value;
    }

    /**
     * Apply if operator to template
     */
    protected function applyIfOperator()
    {
        $ifOperator = $this->getIfOperator();
        $ifOperator->setTemplate($this->getTemplate());
        $ifOperator->setVariableNamespace($this->getVariableNamespace());

        $this->setTemplate($ifOperator->translate($this->getRowData()));
    }

    /**
     * Get pattern
     *
     * @param string $pattern
     * @return string
     */
    protected function getPattern($pattern)
    {
        $namespace = $this->getVariableNamespace();
        return sprintf($pattern, $namespace);
    }

    /**
     * Get variable namespace
     *
     * @return string
     */
    abstract protected function getVariableNamespace();
}
