<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Api\ParserManagement;

/**
 * @method ParserInterface setTemplate(string $template)
 * @method string getTemplate()
 * @method ParserInterface setRowData(array $data)
 * @method array getRowData()
 */
interface ParserInterface
{
    /**
     * Translate attributes injections into string
     *
     * @param array $data
     * @return $this
     */
    public function translate(array $data);
}
