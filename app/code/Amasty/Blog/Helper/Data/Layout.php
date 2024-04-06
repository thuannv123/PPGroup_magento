<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Helper\Data;

/**
 * Class Layout
 */
class Layout extends \Amasty\Blog\Helper\Config
{
    const CONFIG_PATH_LAYOUT = 'layout/%s';

    const CONFIG_PATH_LAYOUT_VALUE = 'layout/%s/%s';

    /**
     * Retrieves Blocks from Config
     *
     * @param $type 'content' | 'sidebar'
     *
     * @return array
     */
    public function getBlocks($type)
    {
        $values = [];
        $config = $this->getConfig();

        if (isset($config[$type])) {
            foreach ($config[$type] as $key => &$item) {
                $item['value'] = $key;
            }

            $values = $config[$type];
        }

        return $values;
    }
}
