<?php

namespace Bodak\CheckoutCustomForm\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Ecommage\CheckoutForm\Observer\Index\Save;

class SetQuote extends Action
{

    protected $checkoutSession;

    private $storeManager;

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $currentStore = $this->storeManager->getStore();    
        if (in_array($currentStore->getCode(), ['arrival', 'departure'])) {
            $street = 'Pickup from '.$currentStore->getName().' Store';
        }
        $dob = null;
        if (isset($data['customer_dob']) && $data['customer_dob']) {            
            $dob = date("Y-m-d", strtotime( str_replace('/','-', $data['customer_dob'])));
        }
        
        $quote = $this->checkoutSession->getQuote();
        $mobile = null;
        if (isset($data['dial_code'])) {
            $mobile = $data['mobile_number']?$data['dial_code'].$data['mobile_number']:null;
        }
        $quote->getBillingAddress()
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($data['email'])
            ->setStreet($street)
            ->setCity('Muharraq')
            ->setPostcode('1714')
            ->setCountryId($data['country_customer'] ?? null)
            ->setCountryCode($data['country_code'])
            ->setTelephone($mobile)
            ->setCustomerPassport($data['passport_no'] ?? null)
            ->setCustomerPhone($mobile)
            ->setCustomerNationality($data['nationality'] ?? null)
            ->setCustomerCountry($data['country_customer'] ?? null)
            ->setCustomerFirstname($data['firstname'])
            ->setCustomerLastname($data['lastname'])
            ->setCustomerDob($dob ?? null)
            ->setCustomerEmail($data['email'])            
            ->setSameAsShipping('1');

        $quote->getShippingAddress()
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($data['email'])
            ->setStreet($street)
            ->setCity('Muharraq')
            ->setPostcode('1714')
            ->setCountryId($data['country_customer'] ?? null)
            ->setCustomerNationality($data['nationality'] ?? null)
            ->setCustomerCountry($data['country_customer'] ?? null)
            ->setCountryCode($data['country_code'])
            ->setTelephone($mobile)
            ->setCustomerPhone($mobile)
            ->setCustomerFirstname($data['firstname'])
            ->setCustomerLastname($data['lastname'])
            ->setCustomerEmail($data['email'])
            ->setCustomerDob($dob ?? null)
            ->setCustomerPassport($data['passport_no'] ?? null);

        $quote->save();
    }
}
