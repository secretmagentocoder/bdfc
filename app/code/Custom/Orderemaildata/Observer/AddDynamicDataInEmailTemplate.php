<?php
namespace Custom\Orderemaildata\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;

class AddDynamicDataInEmailTemplate implements ObserverInterface
{
    protected $currentData;
    protected $storeManagerInterface;
    protected $ImageFactory;
    protected $imageHelper;
    protected $helperData;

    public function __construct(
        \Magento\Sales\Model\Order $orderData,
        \Custom\Orderemaildata\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Helper\ImageFactory $ImageFactory
        ) {
            $this->currentData = $orderData;
            $this->storeManagerInterface = $storeManagerInterface;
            $this->ImageFactory = $ImageFactory;
            $this->imageHelper = $imageHelper;
            $this->helperData = $helper;
        }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getEvent()->getTransport();
        $order = $transport->getOrder();
        $orderData = $this->currentData->load($order->getId());  

        /** @var \Magento\Framework\App\Action\Action $controller */

        $transport = $observer->getTransport();
        $transport['OrderNo'] = $order->getIncrementId();
        $transport['OrderStatus'] = $order->getStatus();
        $transport['OrderDate'] = $order->getCreatedAt();
        $transport['CustomerName'] = $orderData->getCustomerFirstname() . ' ' . $orderData->getCustomerLastname();
        $transport['CustomerEmail'] = $orderData->getCustomerEmail();
        $transport['TotalDiscount'] = $orderData->getDiscountAmount();
        $transport['StoreId'] = $orderData->getStoreId();
        $transport['FlightDate'] = $orderData->getJourneyDate();
        $transport['FlightTime'] = $orderData->getFlightTime();
        $transport['FlightNumber'] = $orderData->getFlightNumber();
        $transport['CollectionTime'] = $orderData->getCollectionTime();
        $transport['CollectionPoint'] = $orderData->getStoreName();
        $transport['SubTotal'] = $orderData->getSubTotal();
        $transport['GrandTotal'] = $orderData->getGrandTotal();
        $transport['TotalTax'] = number_format($orderData->getBaseTaxAmount(),4,'.','.');
        $transport['CustomFee'] = number_format($orderData->getCustomfee(),4,'.','.');
        $transport['TotalAfterDiscount'] = number_format((float) $order->getSubtotal() + (float) $order->getDiscountAmount(),4,'.','.');
        $transport['AfterDiscount'] = $orderData->getSubtotal() - $orderData->getDiscountAmount();

        foreach( $order['addresses'] as $addr ) {
            if( $addr['telephone'] !== null ) {
                $transport['CustomerMobile'] = $addr['telephone'];
            }
        }
        $transport['ProductsData'] = $this->helperData->getLastOrderDetails($order);
    }
}
