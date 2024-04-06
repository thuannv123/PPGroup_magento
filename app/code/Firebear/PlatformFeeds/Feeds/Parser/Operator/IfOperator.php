<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Feeds\Parser\Operator;

use Firebear\PlatformFeeds\Feeds\Parser\Abstracts\AbstractParser;

/**
 * @method IfOperator setVariableNamespace(string $namespace)
 * @method string getVariableNamespace()
 *
 * @SuppressWarnings(PHPMD.EvalExpression)
 * @codingStandardsIgnoreFile
 * phpcs:ignoreFile -- eval is needed. Disable PHP Code Sniffer for this file
 */
class IfOperator extends AbstractParser
{
    /**
     * @var string
     */
    const PATTERN_IF = '/{%[\s]*if(.*?)[\s]*%}(.*?){%[\s]*endif[\s]*%}/si';

    /**
     * @var string
     */
    const PATTERN_CONDITION_PARTS = '/%s.(.*?)[\s]+(.*?)[\s]+([\S]+)([\s]?[|&]{0,2})/si';

    /**
     * @inheritdoc
     */
    public function translate(array $data)
    {
        $this->setRowData($data);

        return preg_replace_callback(
            self::PATTERN_IF,
            [&$this, "replaceCallback"],
            $this->getTemplate()
        );
    }

    protected function replaceCallback($matches)
    {
        $fullPart = $matches[0];
        if (count($matches) < 3) {
            return $fullPart;
        }

        $conditions = $matches[1];
        if (!$this->isTrueStatement($conditions)) {
            return '';
        }

        return $matches[2];
    }

    /**
     * Get if code string
     *
     * @param string $condition
     * @return bool
     */
    protected function isTrueStatement($condition)
    {
        preg_match_all($this->getPatternConditionParts(), $condition, $conditionParts);
        if (count($conditionParts) < 5) {
            return null;
        }

        $eval = '';
        $names = $conditionParts[1];
        $conditions = $conditionParts[2];
        $values = $conditionParts[3];
        $andOr = $conditionParts[4];

        $rowData = $this->getRowData();
        foreach ($names as $key => $varName) {
            if (isset($rowData[$varName])) {
                $variableValue = $this->resolveStringValue($values[$key]);
                $compareValue = $this->resolveStringValue($rowData[$varName]);
                $eval .= ' ' . $variableValue . ' ' . $conditions[$key] . ' ' . $compareValue . ' ';
                if (!empty($andOr[$key])) {
                    $eval .= $andOr[$key];
                }
            }
        }

        try {
            $result = eval('return ' . $eval . ';');
        } catch (\Throwable $throwable) {
            $result = false;
        }

        return $result;
    }

    /**
     * Wrap string value into quotes or returns as is
     *
     * @param float|string $value
     * @return string
     */
    protected function resolveStringValue($value)
    {
        if (!is_numeric($value)) {
            $value = trim($value, '"');
            $value = '"' . $value . '"';
        }

        return $value;
    }

    /**
     * Get pattern condition parts
     *
     * @return string
     */
    protected function getPatternConditionParts()
    {
        return sprintf(self::PATTERN_CONDITION_PARTS, $this->getVariableNamespace());
    }
}
