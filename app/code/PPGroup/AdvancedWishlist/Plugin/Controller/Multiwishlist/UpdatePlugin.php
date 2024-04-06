<?php

namespace PPGroup\AdvancedWishlist\Plugin\Controller\Multiwishlist;

use Magento\Framework\Escaper;
use WeltPixel\AdvancedWishlist\Controller\Multiwishlist\Update;

class UpdatePlugin
{
    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $escaper;

    /**
     * UpdatePlugin constructor.
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper) {
        $this->escaper = $escaper;
    }

    /**
     * @param Update $subject
     */
    public function beforeExecute(Update $subject) {
        $params = $subject->getRequest()->getParams();
        if (isset($params['wishlist-name'])) {
//            $params['wishlist-name'] = $this->escaper->escapeJs($params['wishlist-name']);
            $params['wishlist-name'] = $this->escaper->escapeHtml($params['wishlist-name']);
            $subject->getRequest()->setParams($params);
        }
    }
}
