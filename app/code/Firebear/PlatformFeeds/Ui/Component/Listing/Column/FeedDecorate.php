<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\PlatformFeeds\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Firebear\PlatformFeeds\Model\Mapping\FeedList;

class FeedDecorate extends Column
{
    /**
     * @var FeedList
     */
    protected $feedList;

    /**
     * FeedDecorate constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FeedList $feedList
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FeedList $feedList,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->feedList = $feedList;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $feeds = $this->feedList->getFeeds();
                $item['type'] = $feeds[$item['type_id']] ?? '';
            }
        }

        return $dataSource;
    }
}
