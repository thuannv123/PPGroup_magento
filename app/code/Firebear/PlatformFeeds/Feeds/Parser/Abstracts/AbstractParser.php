<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Feeds\Parser\Abstracts;

use Firebear\PlatformFeeds\Api\ParserManagement\ParserInterface;
use Magento\Framework\DataObject;

/**
 * @method AbstractParser setTemplate(string $template)
 * @method string getTemplate()
 * @method AbstractParser setRowData(array $data)
 * @method array getRowData()
 */
abstract class AbstractParser extends DataObject implements ParserInterface
{

}
