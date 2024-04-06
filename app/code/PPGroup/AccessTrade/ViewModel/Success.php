<?php declare(strict_types=1);

namespace PPGroup\AccessTrade\ViewModel;

use Exception;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PPGroup\AccessTrade\Config\Config;
use Magento\Sales\Api\Data\OrderInterface;
use PPGroup\AccessTrade\Model\System\Config\Source\Method;
use PPGroup\AccessTrade\Model\Session;
use PPGroup\AccessTrade\Model\SessionFactory as AccessTradeSessionFactory;
use Magento\Checkout\Model\Session as CheckoutSession;

class Success implements ArgumentInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var AccessTradeSessionFactory
     */
    protected $accessTradeSessionFactory;

    /**
     * @var OrderInterface|null
     */
    protected $order;

    /**
     * Generic constructor.
     * @param Config $config
     * @param CheckoutSession $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     * @param AccessTradeSessionFactory $accessTradeSessionFactory
     */
    public function __construct(
        Config $config,
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        AccessTradeSessionFactory $accessTradeSessionFactory
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->accessTradeSessionFactory = $accessTradeSessionFactory;
    }


    /**
     * @return OrderInterface
     */
    protected function getOrder(): ?OrderInterface
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if ($this->order === null && $order) {
            $this->order = $this->orderRepository->get($order->getId());
        }
        return $this->order;
    }

    /**
     * @return bool
     */
    public function isTrackingTag(): bool
    {
        return $this->config->getIntegrationMethod() === Method::TRACKING_TAG;
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getPaymentLabel(OrderInterface $order): string
    {
        $payment = $order->getPayment();
        return $payment ? $payment->getMethod() : '';
    }

    /**
     * Current rk from session
     *
     * @return string
     */
    public function getRk(): string
    {
        return (string)$this->accessTradeSessionFactory->create()->getData(Session::ACCESS_TRADE_SESSION);
    }

    /**
     * @return bool
     */
    public function hasOrder(): bool
    {
        try {
            $this->getOrder();
        } catch (Exception $e) {
            return false;
        }

        return (bool)$this->order;
    }

    /**
     * Get Campaign ID for container
     *
     * @return string
     */
    public function getCampaignId(): string
    {
        return $this->config->getCampaignId();
    }

    /**
     * Get Result ID for container
     *
     * @return int
     */
    public function getResultId(): int
    {
        return $this->config->getResultId();
    }

    /**
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->hasOrder() ? (string)$this->getOrder()->getIncrementId() : '';
    }

    /**
     * @return float
     */
    public function getSalesAmount(): float
    {
        $amount = $this->hasOrder() ? $this->getOrder()->getSubtotal() : 0.00;

        if (!$amount) {
            return 0.00;
        }

        if ($this->config->getTaxMode() == \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX) {
            $amount = $amount / (107 / 100);
        }

        return round($amount, 2);
    }

    /**
     * @return float
     */
    public function getDiscountAmount(): float
    {
        $amount = $this->hasOrder() ? abs($this->getOrder()->getDiscountAmount()) : 0.00;

        if (!$amount) {
            return 0.00;
        }

        if ($this->config->getTaxMode() == \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX) {
            $amount = $amount / (107 / 100);
        }

        return round($amount, 2);
    }

    /**
     * @return string|null
     */
    public function getCurrencyCode(): ?string
    {
        return $this->hasOrder() ? $this->getOrder()->getOrderCurrencyCode() : '';
    }
}
