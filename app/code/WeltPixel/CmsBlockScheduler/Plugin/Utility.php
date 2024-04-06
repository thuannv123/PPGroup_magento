<?php

namespace WeltPixel\CmsBlockScheduler\Plugin;

class Utility extends \WeltPixel\Backend\Plugin\Utility
{
    /**
     * @return string
     */
    protected function getModuleName()
    {
        return $this->convertToString(
            [
                '87', '101', '108', '116', '80', '105', '120', '101', '108', '95', '67', '109', '115', '66', '108',
                '111', '99', '107', '83', '99', '104', '101', '100', '117', '108', '101', '114'
            ]
        );
    }

    /**
     * @return array
     */
    protected function _getAdminPaths()
    {
        return [
            $this->convertToString([
                '115', '121', '115', '116', '101', '109', '95', '99', '111', '110', '102', '105', '103', '47',
                '101', '100', '105', '116', '47', '115', '101', '99', '116', '105', '111', '110', '47', '119',
                '101', '108', '116', '112', '105', '120', '101', '108', '95', '99', '109', '115', '98', '108',
                '111', '99', '107', '115', '99', '104', '101', '100', '117', '108', '101', '114', '95', '99',
                '111', '110', '102', '105', '103'
            ]),
            $this->convertToString([
                '99', '109', '115', '98', '108', '111', '99', '107', '115', '99', '104', '101', '100', '117', '108',
                '101', '114', '47', '116', '97', '103'
            ])
        ];
    }
}