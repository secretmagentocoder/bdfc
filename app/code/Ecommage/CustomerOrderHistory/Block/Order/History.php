<?php

namespace Ecommage\CustomerOrderHistory\Block\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Widget\Block\BlockInterface;

class History  extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = 'Ecommage_CustomerOrderHistory::order/history.phtml';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;

    /**
     * @var CollectionFactoryInterface
     */
    private $orderCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param array $data
     */
    public function __construct(
        Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        array $data = []
    ) {
        $this->_registry= $registry;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Order History'));
    }

    /**
     * Provide order collection factory
     *
     * @return CollectionFactoryInterface
     * @deprecated 100.1.1
     */
    private function getOrderCollectionFactory()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->orderCollectionFactory;
    }

    /**
     * Get customer orders
     *
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        $text = $this->_registry->registry('text_search');
        if ($text){
            $this->orders = $this->getOrderCollectionFactory()->create($customerId)->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'increment_id',
                ['like' => '%' .$text .'%']
            )->setOrder(
                'created_at',
                'desc'
            );
        } else {
            $this->orders = $this->getOrderCollectionFactory()->create($customerId)->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'status',
                ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
            )->setOrder(
                'created_at',
                'desc'
            );
        }

        return $this->orders;
    }

    /**
     * Get customer orders
     *
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrdersByStatus($status)
    {
        $orders = [];
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        $text = $this->_registry->registry('text_search');
        if ($text){
            $orders = $this->getOrderCollectionFactory()->create($customerId)->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'increment_id',
                ['in' => $text]
            )->setOrder(
                'created_at',
                'desc'
            );
        } else {
            $orders = $this->getOrderCollectionFactory()->create($customerId)->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'status',
                ['in' => $status]
            )->setOrder(
                'created_at',
                'desc'
            );
        }
        return $orders;
    }

    /**
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOrders() && !$this->getLayout()->getBlock('sales.order.history.pager')) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'sales.order.history.pager'
            )->setCollection(
                $this->getOrders()
            );
            $this->setChild('pager', $pager);
            $this->getOrders()->load();
        }
        return $this;
    }

    /**
     * Get Pager child block output
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get order view URL
     *
     * @param object $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }

    /**
     * Get order track URL
     *
     * @param object $order
     * @return string
     * @deprecated 102.0.3 Action does not exist
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getTrackUrl($order)
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        trigger_error('Method is deprecated', E_USER_DEPRECATED);
        return '';
    }

    /**
     * Get reorder URL
     *
     * @param object $order
     * @return string
     */
    public function getReorderUrl($order)
    {
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }

    /**
     * Get customer account URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * Get message for no orders.
     *
     * @return \Magento\Framework\Phrase
     * @since 102.1.0
     */
    public function getEmptyOrdersMessage()
    {
        return __('You have placed no orders.');
    }

    public function getBlockDisplayHtml()
    {
        return $this->getLayout()->createBlock('Ecommage\CustomerOrderHistory\Block\DisplayVideo')->toHtml();
    }
}
