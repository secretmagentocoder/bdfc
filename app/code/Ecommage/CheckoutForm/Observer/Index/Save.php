<?php

namespace Ecommage\CheckoutForm\Observer\Index;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Save implements ObserverInterface
{

    const FIRST_NAME_DEFAULT = 'Fname';
    const LAST_NAME_DEFAULT = 'Lname';
    const DOB_DEFAULT = '18/01/1999';
    const COUNTRY_ID_DEFAULT = 'BH';
    const REGION_ID_DEFAULT = '570';
    const REGION_DEFAULT = 'BH';
    const CITY_DEFAULT = 'Bahrain';
    const POSTCODE_DEFAULT = '1714';
    const STREET_DEFAULT = 'Pickup from Bdfc';
    const TELEPHONE_DEFAULT = '+97317303030';

    public function __construct
    (
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerInterface,
        \Magento\Customer\Model\AddressFactory $addressFactory
    )
    {
        $this->customerInterfaceFactory = $customerInterface;
        $this->addressFactory = $addressFactory;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getData('customer');
        if (empty($customer->getDefaultBilling()) && empty($customer->getDefaultShipping()) && !empty($customer->getId()))
        {
            $address = $this->setShipping($customer->getId());
            $customer->setDefaultBilling($address->getId());
            $customer->setDefaultShipping($address->getId());
            $this->customerInterfaceFactory->save($customer);
        }
    }


    public function setShipping($customerId)
    {
        $address = $this->addressFactory->create();
        if ($customerId){
            try {
                $address->setFirstname(self::FIRST_NAME_DEFAULT)
                        ->setLastname(self::LAST_NAME_DEFAULT)
                        ->setCountryId(self::COUNTRY_ID_DEFAULT)
                        ->setRegionId(self::REGION_ID_DEFAULT)
                        ->setRegion(self::REGION_DEFAULT)
                        ->setCity(self::CITY_DEFAULT)
                        ->setPostcode(self::POSTCODE_DEFAULT)
                        ->setCustomerId($customerId)
                        ->setStreet(self::STREET_DEFAULT)
                        ->setTelephone(self::TELEPHONE_DEFAULT);
                $address->save();
                return $address;
            }catch (\Exception $e){
                $this->logger->error($e->getMessage());
            }
        }
    }

}
