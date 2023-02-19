<?php
namespace Custom\CartRule\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;
use Ceymox\Navconnector\Model\CustomDuty\CustomDutyManager;

class ChangeTaxTotal implements ObserverInterface
{
    public function __construct(
        CustomDutyManager $customDutyManager        
        
    ) {
        $this->customDutyManager = $customDutyManager;
        
    }
    public function execute(Observer $observer)
    {
        // Fetch Address related data
        // $shippingAssignment = $observer->getEvent()->getShippingAssignment();
        // $address = $shippingAssignment->getShipping()->getAddress();

        // fetch quote data
        // $quote = $observer->getEvent()->getQuote();

        // fetch totals data
        // $total = $observer->getEvent()->getTotal();

        // fetch address total data
        $total = $observer->getData('total');

        $total_custom_duty_fee = $total->getTotalAmount('customfee');

        $total_custom_duty_vat = $this->getCustomDutyVat($total_custom_duty_fee);

        //make sure tax value exist
        // if (count($total->getAppliedTaxes()) > 0) {
        //     $total->addTotalAmount('tax', $this->total_custom_duty_vat);
        // }
        $total->addTotalAmount('tax', $total_custom_duty_vat);
        $total->setGrandTotal($total->getGrandTotal()+$total_custom_duty_vat);
        $total->setBaseGrandTotal($total->getBaseGrandTotal()+$total_custom_duty_vat);

        return $this;
    }


    /**
    * Get getCustomDutyVat
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomDutyVat($total_custom_duty_fee)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $store_id = $storeManager->getStore()->getId();

        $total_custom_duty_vat = 0;

        $vat_percent_for_excise_duty = $this->getCustomDutyVatPercent($store_id);

        if (is_numeric($total_custom_duty_fee) && is_numeric($vat_percent_for_excise_duty)) {
            $total_custom_duty_vat = (($total_custom_duty_fee * $vat_percent_for_excise_duty) / 100);
        }

        if (!is_numeric($total_custom_duty_vat)) {
            $total_custom_duty_vat = 0;
        }

        return $total_custom_duty_vat;
    }

    /**
    * Get getCustomDutyVatPercent
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomDutyVatPercent($store_id)
    {
        return $this->customDutyManager->getCustomDutyValue();
    }
}
