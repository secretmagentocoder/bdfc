<?php

namespace Bodak\CheckoutCustomForm\Observer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Bodak\CheckoutCustomForm\Api\Data\CustomFieldsInterface;
use Bodak\CheckoutCustomForm\Api\Data\DepartureFieldsInterface;
use Magento\Store\Model\StoreManager;

class SubmitCheckout implements ObserverInterface
{
    public function __construct
    (
        CustomerSession  $customerSession,
        CheckoutSession $checkoutSession,
        StoreManager $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
    }

    public function execute(Observer $observer)
    {
        $quote = $observer->getData('quote');
        $flightDetails = ($this->checkoutSession->getFligtDetails()) ? $this->checkoutSession->getFligtDetails() : null;
        $onHandDetails = ($this->checkoutSession->getQtyOnHand()) ? $this->checkoutSession->getQtyOnHand() : null;
        $anotherPerson = $this->checkoutSession->getAnotherPersonInfo();
        $currentStore = $this->storeManager->getStore()->getCode();

        $quoteAddress = $this->checkoutSession->getQuote()->getShippingAddress();
        if (! $quoteAddress->getTelephone() && $quoteAddress->getCustomerPhone()) {
            $quoteAddress->setTelephone($quoteAddress->getCustomerPhone());
        }
        if($currentStore == 'arrival'){
            if (isset($flightDetails)) {
                $quote->setData(CustomFieldsInterface::ARRIVAL_FLIGHT_TIME, $flightDetails[0]['Flight_Time']);
                $quote->setData(CustomFieldsInterface::ARRIVAL_FLIGHT_NAME, $flightDetails[0]['Airline_Name']);
                $quote->setData(CustomFieldsInterface::ARRIVAL_FLIGHT_DATE, $flightDetails[0]['Flight_Date']);
                $quote->setData(CustomFieldsInterface::COLLECTION_DATE, $flightDetails[0]['Flight_Date']);
            }
            if(isset($onHandDetails)){
                $quote->setData(CustomFieldsInterface::ARRIVAL_QUANTITY_ON_HAND, $onHandDetails);
            }
            if(isset($anotherPerson)){
                $quote->setData(
                    CustomFieldsInterface::ARRIVAL_COLLECTION_POINT,
                    $anotherPerson['formFlight'][CustomFieldsInterface::ARRIVAL_COLLECTION_POINT]
                );
                $quote->setData(
                    CustomFieldsInterface::ARRIVAL_ANOTHER_PERSON_PHONE,
                    $anotherPerson['formFlight'][CustomFieldsInterface::ARRIVAL_ANOTHER_PERSON_PHONE]
                );
            }
        }
        if($currentStore == 'departure'){
            if (isset($flightDetails)) {
                $quote->setData(DepartureFieldsInterface::DEPARTURE_FLIGHT_TIME, $flightDetails[0]['Flight_Time']);
                $quote->setData(DepartureFieldsInterface::DEPARTURE_FLIGHT_NAME, $flightDetails[0]['Airline_Name']);
                $quote->setData(DepartureFieldsInterface::DEPARTURE_FLIGHT_DATE, $flightDetails[0]['Flight_Date']);
                $quote->setData(CustomFieldsInterface::COLLECTION_DATE, $flightDetails[0]['Flight_Date']);
            }
            if(isset($anotherPerson)){
                $quote->setData(
                    DepartureFieldsInterface::DEPARTURE_SELECT_YOUR_LOUNGE, 
                    $anotherPerson['formFlight'][DepartureFieldsInterface::DEPARTURE_SELECT_YOUR_LOUNGE]
                );
                $quote->setData(
                    DepartureFieldsInterface::DEPARTURE_ANOTHER_PERSON_PHONE, 
                    $anotherPerson['formFlight'][DepartureFieldsInterface::DEPARTURE_ANOTHER_PERSON_PHONE]
                );
            }
        }
    }
}
