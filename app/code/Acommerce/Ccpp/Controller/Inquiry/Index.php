<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 * PHP version 5
 *
 * @category Acommerce_CPMSConnect
 * @package  Acommerce
 * @author   Ranai L <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.Acommerce.asia
 */

namespace Acommerce\Ccpp\Controller\Inquiry;

/**
 * Trigger Cronjob
 *
 * @category Acommerce_CPMSConnect
 * @package  Acommerce
 * @author   Ranai L <ranai@acommerce.asia>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.Acommerce.asia
 */
class Index extends \Magento\Framework\App\Action\Action
{
     /**
      * Result Page Factory
      *
      * @var \Magento\Framework\View\Result\PageFactory
      */
    protected $_resultPageFactory;

     /**
      * Scope Config
      *
      * @var \Magento\Framework\App\Config\ScopeConfigInterface
      */
    protected $_scopeConfig;

    /**
     * Url Interface
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlInterface;

    /**
     * Response Factory
     *
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $_responseFactory;


    /**
     * Constructor
     *
     * @param Context              $context           Context
     * @param PageFactory          $resultPageFactory Result Page Factory
     * @param ScopeConfigInterface $scopeConfig       Scope Config
     * @param ResponseFactory      $responseFactory   Response Factory
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResponseFactory $responseFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_scopeConfig       = $scopeConfig;
        $this->_urlInterface      = $context->getUrl();
        $this->_responseFactory   = $responseFactory;
        parent::__construct($context);
    }//end __construct()


    /**
     * Function Execute()
     *
     * @return void
     */
    public function execute()
    {
        //echo "test";
        $cronjob = $this->_objectManager->create('\Acommerce\Ccpp\Cron\InquiryTransaction');
        $cronjob->execute();
    }//end execute()
}//end class
