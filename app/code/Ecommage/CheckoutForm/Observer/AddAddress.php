<?php

namespace Ecommage\CheckoutForm\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddAddress implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $address;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\AddressFactory $address
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(\Magento\Framework\App\Request\Http     $request,
                                \Magento\Checkout\Model\Session         $checkoutSession,
                                \Magento\Customer\Model\AddressFactory  $address,
                                \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        $this->customerFactory = $customerFactory;
        $this->address = $address;
        $this->_checkoutSession = $checkoutSession;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $id = $observer->getData()['customer']->getId();
        $customer = $this->customerFactory->create()->load($id);
        $shippingAddress = $this->address->create();
        $newData = [
            'is_active' => 1,
            'parent_id' => $id,
            'city' => 'Bahrain',
            'country_id' => 'BH',
            'firstname' => $customer->getData('firstname'),
            'lastname' => $customer->getData('lastname'),
            'postcode' => '100',
            'street' => 'street' . "\n" . 'street' . "\n" . 'street' . "\n" . 'street',
            'telephone' => '0234567890',
        ];
        $shippingAddress->addData($newData);
        $saveAddress = $shippingAddress->save();
        $billing = $saveAddress->getEntityId();
        $data = [
            'default_billing' => $billing,
            'default_shipping' => $billing
        ];
        $customer->addData($data);
        $customer->save();


    }
}
