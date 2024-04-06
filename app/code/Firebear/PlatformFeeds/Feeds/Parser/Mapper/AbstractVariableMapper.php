<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Feeds\Parser\Mapper;

abstract class AbstractVariableMapper
{
    /**
     * Get replacement for mapped variable
     *
     * @param string $value
     * @return string
     */
    abstract public function map($value);
}
