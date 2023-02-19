<?php

namespace Custom\GuestRegistration\Observer;

class Convertguest implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Api\OrderCustomerManagementInterface
     */
    protected $orderCustomerService;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Convertguest constructor.
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomerService
     * @param \Magento\Customer\Model\CustomerFactory $customer
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomerService,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_orderFactory = $orderFactory;
        $this->orderCustomerService = $orderCustomerService;
        $this->_customer = $customer;
        $this->orderRepository = $orderRepository;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();

        if (count($orderIds)) {

            $orderId = $orderIds[0];
            $order = $this->_orderFactory->create()->load($orderId);

            $shippingAddress = $order->getShippingAddress();
            $phone_number = $order->getMobileNumber();

            $customer = $this->_customer->create();
            $customer->setWebsiteId($this->_storeManager->getStore()->getWebsiteId());
            $customer->loadByEmail($order->getCustomerEmail());

            if (! $order->getCustomerId()) {
            /*Convert guest to customer*/
            if ($order->getId() && !$customer->getId()) {
                /*New Customer*/
                    // $this->orderCustomerService->create($orderId);
                    try {                
                            $customer = $this->_customer->create();
                            $customer->setWebsiteId($this->_storeManager->getStore()->getWebsiteId())
                    ->setStore($this->_storeManager->getStore())
                    ->setFirstname($order->getCustomerFirstname())
                    ->setLastname($order->getCustomerLastname())
                    ->setEmail($order->getCustomerEmail())
                                ->setMobileNumber($phone_number)
                                ->setCountryCode($order->getCountryCode())
                                ->setPassword($order->getCustomerFirstname().'123!@#');
    ;
                $customer->save();

                $order->setCustomerId($customer->getId());
                $order->setCustomerIsGuest(0);
                            $this->orderRepository->save($order);
                        } catch(\Exception $e) {                       
                            $this->logger->critical($e->getMessage());
                        }
                                
            } else {
                /*Registered customer guest checkout*/
                $order->setCustomerId($customer->getId());
                $order->setCustomerIsGuest(0);
                $this->orderRepository->save($order);
            }
            }       
        }
    }
}
