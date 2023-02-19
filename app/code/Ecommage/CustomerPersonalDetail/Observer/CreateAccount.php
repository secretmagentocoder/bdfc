<?php

namespace Ecommage\CustomerPersonalDetail\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CreateAccount implements ObserverInterface
{

    protected $storeManager;

    protected $customerRepository;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository)
    {
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
    }

    public function execute(Observer $observer){
        $mobileNumber = $observer->getData('account_controller')->getRequest()->getParam('mobile_number');
        $prefix = $observer->getData('account_controller')->getRequest()->getParam('prefix');
        $countryCode = $observer->getData('account_controller')->getRequest()->getParam('country_code');
        $nationalId = $observer->getData('account_controller')->getRequest()->getParam('nationality');
        
        $customer = $observer->getCustomer();
        $customer->setCustomAttribute('mobile_number',$mobileNumber);
        $customer->setPrefix($prefix);
        $customer->setCustomAttribute('country_code',$countryCode);
        $customer->setCustomAttribute('national_id',$nationalId);
        $this->customerRepository->save($customer);
    }
}