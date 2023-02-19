<?php

namespace Ecommage\CheckoutForm\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;

class SetCustomAttributeToQuote extends Action implements HttpPostActionInterface
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

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_date;


    protected $storeManager;

    /**
     * @param Context $context
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param CustomerFactory $customerFactory
     * @param AddressFactory $addressFactory
     * @param \Magento\Customer\Model\Session $customer
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Checkout\Model\Session $checkoutSession,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        \Magento\Customer\Model\Session $customer,
        \Magento\Store\Model\StoreManagerInterface $storeManager

    )
    {
        $this->storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->customer = $customer;
        $this->_date = $date;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $currentStore = $this->storeManager->getStore()->getCode();
        $quote = $this->_checkoutSession->getQuote()->getShippingAddress();
        $requestData = $this->getRequest()->getParams();

        if (!$this->customer->isLoggedIn()) {
            $requestData = $this->setData($requestData['data']);
            $requestData['data']['save_address'] = 1;
        }else{
            $this->_checkoutSession->getQuote()->setCustomerIsGuest(0)->save();
        }

        $this->_checkoutSession->setSaveAddressStep2($requestData['data']['save_address']?? 0 );

        if ($currentStore == 'home_delivery') {
            $quote->setData('customer_street', implode(" , ", $requestData['data']['street']));
        }


        $dobFormat = $this->_date->date(strtotime($requestData['data']['customer_dob']))->format('Y-m-d');

        $quote->setCustomerDob($dobFormat)
              ->setCustomerNationality($requestData['data']['nationality'])
              ->setCustomerFirstname($requestData['data']['firstname'])
              ->setCustomerLastname( $requestData['data']['lastname'])
              ->setCustomerEmail($requestData['data']['email'])
              ->setCustomerPhone($requestData['data']['mobile_number'])
              ->setCountryCode($requestData['data']['country_code'])
              ->setMobileNumber($requestData['data']['mobile_number'])
              ->setCustomerPassport($requestData['data']['passport_no'] ?? null)
              ->setCustomerCountry($requestData['data']['country_customer'] ?? null);
        if (!$this->customer->isLoggedIn())
        {
            $quote->setCheckoutMethod('guest');
        }

        $quote->save();
    }

    public function setData($request)
    {
        $arr = [];
        if ($request)
        {
            foreach ($request as $key => $item)
            {
              if($item['name'] == 'street[]')
              {
                    $arr['data']['street'][] = $item['value'];
              }
                $arr['data'][$item['name']] =  $item['value'];
            }
        }
        
        return $arr;
    }
}
