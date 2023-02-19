<?php
/**
 * @package Bdfc_General
 */
declare(strict_types=1);

namespace Bdfc\General\Block\Order;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;

class CollectionDetails extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Registry
     */
    private $coreRegistry = null;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param OrderFactory $orderFactory
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        OrderFactory $orderFactory,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->orderFactory = $orderFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Order information by id
     */
    public function getOrderInfo()
    {
        $orderId = $this->getOrder()->getId();
        $order = $this->orderFactory->create()->load($orderId);
        return $order;
    }

    /**
     * Get current order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }
    
    /**
     * Flight Number
     */
    public function getFlightNumber()
    {
        return $this->getOrderInfo()->getFlightNumber();
    }

    /**
     * Flight Time
     */
    public function getFlightTime()
    {
        return $this->getOrderInfo()->getFlightTime();
    }

    /**
     * Collection Point
     */
    public function getCollectionPoint()
    {
        return $this->getOrderInfo()->getCollectionPoint();
    }

    /**
     * Collection Time
     */
    public function getCollectionTime()
    {
        return $this->getOrderInfo()->getCollectionTime();
    }

    /**
     * Collection Date
     */
    public function getCollectionDate()
    {
        return $this->getOrderInfo()->getCollectionDate();
    }

    /**
     * Current Store code
     */
    public function getCurrentStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }
}
