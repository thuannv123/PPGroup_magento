<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Feeds\Parser\Variable;

/**
 * @method Modifier setRowData(array $data)
 * @method array getRowData()
 *
 * @codingStandardsIgnoreFile
 * phpcs:ignoreFile -- empty catch, call_user_func_array is needed
 */
class Modifier
{
    /**
     * Custom PHP function is used as value modifier
     *
     * @var string
     */
    const CUSTOM_MODIFIER_FLAG = 'custom';

    /**
     * Modifiers list
     *
     * @var array
     */
    const MODIFIER_CONFIGURATION = [
        // Core PHP functions
        'trim' => [
            'arguments' => 2
        ],
        'ltrim' => [
            'arguments' => 2
        ],
        'rtrim' => [
            'arguments' => 2
        ],
        'htmlspecialchars' => [
            'arguments' => 1
        ],
        'strip_tags' => [
            'arguments' => 2
        ],
        'substr' => [
            'arguments' => 3
        ],
        'ucfirst' => [
            'arguments' => 1
        ],
        'str_replace' => [
            'arguments' => 3
        ],
        'ceil' => [
            'arguments' => 1
        ],
        'floor' => [
            'arguments' => 1
        ],
        'round' => [
            'arguments' => 2
        ],
        'nl2br' => [
            'arguments' => 1
        ],

        // Custom functions
        'removeLineBreaks' => self::CUSTOM_MODIFIER_FLAG
    ];

    /**
     * Modifier pattern to find modifiers in template
     *
     * @var string
     */
    const MODIFIER_PATTERN = '/([a-z_]+)\((.*?)\)/si';

    /**
     * Apply modifiers to variable
     *
     * @param string $value
     * @param string $template
     * @return string
     */
    public function modify($value, $template)
    {
        $modifiers = $this->getModifiers($template);
        if (empty($modifiers)) {
            return $value;
        }

        foreach ($modifiers as $modifier) {
            $value = $this->applyModifier($value, $modifier);
        }

        return $value;
    }

    /**
     * Get modifiers
     *
     * @param string $template
     * @return array
     */
    protected function getModifiers($template)
    {
        $result = [];
        $template = trim($template, '{}');
        $modifiers = explode('|', $template);
        if (count($modifiers) < 2) {
            return $result;
        }

        for ($i = 1, $count = count($modifiers); $i < $count; $i++) {
            $result[] = $modifiers[$i];
        }

        return $result;
    }

    /**
     * Apply modifier
     *
     * @param string $value
     * @param string $modifier
     * @return string
     */
    protected function applyModifier($value, $modifier)
    {
        $result = preg_match_all(self::MODIFIER_PATTERN, $modifier, $matches);
        if (!$result) {
            return $value;
        }

        $modifier = trim($matches[1][0]);
        if (!isset(self::MODIFIER_CONFIGURATION[$modifier])) {
            return $value;
        }

        $configuration = self::MODIFIER_CONFIGURATION[$modifier];
        if ($configuration == self::CUSTOM_MODIFIER_FLAG) {
            try {
                $value = $this->$modifier($value);
            } catch (\Throwable $throwable) {
                // Do nothing
            }
        } else {
            $arguments = explode(',', $matches[2][0]);
            if ($modifier == 'str_replace') {
                $arguments[] = $value;
            } else {
                array_unshift($arguments, $value);
                $arguments = array_slice($arguments, 0, $configuration['arguments'], true);
            }

            try {
                $value = call_user_func_array($modifier, $arguments);
            } catch (\Throwable $throwable) {
                // Do nothing
            }
        }

        return $value;
    }

    /**
     * Remove all line breaks
     *
     * @param string $value
     * @return string
     */
    protected function removeLineBreaks($value)
    {
        // Replace the line breaks with a space
        $value = str_replace(['\r', '\n'], ' ', $value);
        // Replace multiple spaces with one
        $value = preg_replace('!\s+!', ' ', $value);

        return $value;
    }
}
