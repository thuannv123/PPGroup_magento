<?php
declare(strict_types=1);

namespace Acommerce\Ccpp\Model\Payment;

use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Acommerce\Ccpp\Model\Ui\ConfigProvider;
use Magento\Sales\Model\Order;

class Qrcode extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "qrcode";
    protected $_isOffline = true;

    public function getConfigPaymentAction()
    {
        return 'order';
    }

    /**
     * Flag if we need to run payment initialize while order place
     *
     * @return bool
     *
     */
    public function isInitializeNeeded()
    {
        return true;
    }

    /**
     * @param string $paymentAction
     * @param \Magento\Framework\DataObject $stateObject
     * @return $this|Qrcode
     */
    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setData([
            'state' => Order::STATE_PENDING_PAYMENT,
            'status' =>'pending_payment'
        ]);
        return $this;
    }

    /**
     * Get Order Place Redirec tUrl
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return ConfigProvider::TRANSACTION_DATA_URL;
    }
}

