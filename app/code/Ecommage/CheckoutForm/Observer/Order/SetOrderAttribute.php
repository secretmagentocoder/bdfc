<?php
namespace Ecommage\CheckoutForm\Observer\Order;

use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SetOrderAttribute implements ObserverInterface
{
    /**
     * @var
     */
    protected $_customerFactory;

    /**
     * @var
     */
    protected $_addressFactory;

    /**
     * @var
     */
    protected $customer;

    protected $request;

    protected $_checkoutSession;

    protected $address;

    protected $storeManager;


    public function __construct(\Magento\Framework\App\Request\Http $request,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                \Magento\Sales\Model\Order\AddressFactory $address,
                                CustomerFactory                 $customerFactory,
                                AddressFactory                  $addressFactory,
                                \Magento\Customer\Model\Session $customer,
                                \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->customer = $customer;
        $this->address = $address;
        $this->_checkoutSession = $checkoutSession;
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        $quote = $this->_checkoutSession->getQuote()->getShippingAddress();
        $dataStreet = explode(' , ', $quote->getData('street'));
        $checkSaveAddressStep2 = $this->_checkoutSession->getSaveAddressStep2();

        $currentStore = $this->storeManager->getStore()->getCode();
        if ($currentStore == 'home_delivery' && !empty($checkSaveAddressStep2) && !empty($this->customer->getId()) ) {
            $customer = $this->_customerFactory->create()->load($this->customer->getId());
            $shippingAddressId = $customer->getDefaultShipping();
            $shippingAddress = $this->_addressFactory->create();
            if ($shippingAddressId) {
                $shippingAddress = $shippingAddress->load($shippingAddressId);
            }
            $billingAddressId = $customer->getDefaultBilling();
            $billingAddress = $this->_addressFactory->create();
            if ($billingAddressId) {
                $billingAddress = $billingAddress->load($billingAddressId);
            }
            $newData = [
                'street' => $dataStreet,
            ];
            if ($this->_checkoutSession->getQuote()->getIsVirtual()) {
                $billingAddress->addData($newData);
                $billingAddress->save();
            } else {
                $shippingAddress->addData($newData);
                $shippingAddress->save();
            }
        }
        // get data from quote saved at ShippingInformation
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        $quote = $this->_checkoutSession->getQuote()->getShippingAddress();
        // set data personal
        $order->setCustomerFirstname($quote->getData('firstname'))
            ->setCustomerLastname($quote->getData('lastname'))
            ->setCustomerEmail($quote->getData('email'))
            ->setCustomerDob($quote->getData('customer_dob'));
        $order->setData('country_code', $quote->getData('country_code'));
        $order->setData('mobile_number',$quote->getData('telephone'));
        $order->setData('customer_nationality', $quote->getData('customer_nationality'));
        $order->setData('customer_passport', $quote->getData('customer_passport'));
        $order->setData('customer_country', $quote->getData('customer_country'));
        $order->getBillingAddress()->setTelephone($quote->getData('telephone'));

        if ($this->_checkoutSession->getQuote()->getIsVirtual()) {
            $order->getBillingAddress()->setStreet($dataStreet);
        } else {
            $order->getShippingAddress()->setStreet($dataStreet);
        }

    }
}
