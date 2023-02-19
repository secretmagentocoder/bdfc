<?php

namespace Custom\CartRule\Controller\Sales;

class UpdateArrivalDate extends \Magento\Framework\App\Action\Action
{
    private $orderResource;
    private $orderFactory;
	protected $_pageFactory;
    protected $orderRepository;
    protected $resultJsonFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\Spi\OrderResourceInterface $orderResource,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->resultJsonFactory = $resultJsonFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
        $data = $this->getRequest()->getParams();
        // print_r($data);die;
        if(is_array($data) || !empty($data))
        {
            $incrementId =  isset($data['orderId'])?$data['orderId']:'';
            $order = $this->getOrder($incrementId);            
            $originalDate =  isset($data['updateArrivalDate'])?$data['updateArrivalDate']:'';
            $newDate = date("d/m/Y", strtotime($originalDate));
            $order->setArrivalFlightDate($newDate);
            $order->save();
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData(['message' => 'Updated Successfully']);
        }  
	}

    public function getOrder($incrementId)
    {
        $orderModel = $this->orderFactory->create();
        $order = $orderModel->loadByIncrementId($incrementId);
        return $order;
    }
}