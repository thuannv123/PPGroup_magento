<?php

namespace PPGroup\AccessTrade\Model\System;

/**
 * Class Dumper as helper for dumping data intended to replace the buggy PHP function var_dump and print_r.
 */
class Dumper
{
    /**
     * @var array
     */
    private static $objects = [];

    /**
     * @var string
     */
    private static $output = '';

    /**
     * @var int
     */
    private static $depth;

    /**
     * Converts a variable into a string representation.
     * This method achieves the similar functionality as var_dump and print_r
     * but is more robust when handling complex objects such as PRADO controls.
     * @param $var
     * @param int $depth
     * @param bool $highlight
     * @return string the string representation of the variable
     */
    public function dump($var, $depth = 10, $highlight = false): string
    {
        self::$output = '';
        self::$objects = [];
        self::$depth = $depth;
        self::dumpInternal($var, 0);
        if ($highlight) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $result = highlight_string("<?php\n" . self::$output, true);
            return preg_replace('/&lt;\\?php<br \\/>/', '', $result, 1);
        } else {
            return self::$output;
        }
    }

    protected function dumpInternal($var, $level)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        switch (gettype($var)) {
            case 'boolean':
                self::$output .= $var ? 'true' : 'false';
                break;
            case 'double':
            case 'integer':
                self::$output .= "$var";
                break;
            case 'string':
                self::$output .= "'$var'";
                break;
            case 'resource':
                self::$output .= '{resource}';
                break;
            case 'NULL':
                self::$output .= "null";
                break;
            case 'unknown type':
                self::$output .= '{unknown}';
                break;
            case 'array':
                if (self::$depth <= $level) {
                    self::$output .= 'array(...)';
                } else {
                    if (empty($var)) {
                        self::$output .= 'array()';
                    } else {
                        $keys = array_keys($var);
                        $spaces = str_repeat(' ', $level * 4);
                        self::$output .= "array\n" . $spaces . '(';
                        foreach ($keys as $key) {
                            self::$output .= "\n" . $spaces . "    [$key] => ";
                            self::$output .= self::dumpInternal($var[$key], $level + 1);
                        }
                        self::$output .= "\n" . $spaces . ')';
                    }
                }
                break;
            case 'object':
                if (($id = array_search($var, self::$objects, true)) !== false) {
                    self::$output .= get_class($var) . '#' . ($id + 1) . '(...)';
                } else {
                    if (self::$depth <= $level) {
                        self::$output .= get_class($var) . '(...)';
                    } else {
                        $id = array_push(self::$objects, $var);
                        $className = get_class($var);
                        $members = (array)$var;
                        $keys = array_keys($members);
                        $spaces = str_repeat(' ', $level * 4);
                        self::$output .= "$className#$id\n" . $spaces . '(';
                        foreach ($keys as $key) {
                            $keyDisplay = strtr(trim($key), ["\0" => ':']);
                            self::$output .= "\n" . $spaces . "    [$keyDisplay] => ";
                            self::$output .= self::dumpInternal($members[$key], $level + 1);
                        }
                        self::$output .= "\n" . $spaces . ')';
                    }
                }
                break;
        }
    }
}
