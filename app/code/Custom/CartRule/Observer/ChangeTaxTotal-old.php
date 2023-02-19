<?php
namespace Custom\CartRule\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;

class ChangeTaxTotal implements ObserverInterface
{
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

        $total_excise_duty_vat = $this->getExciseDutyVat();

        //make sure tax value exist
        // if (count($total->getAppliedTaxes()) > 0) {
        //     $total->addTotalAmount('tax', $this->total_excise_duty_vat);
        // }
        $total->addTotalAmount('tax', $total_excise_duty_vat);
        $total->setGrandTotal($total->getGrandTotal()+$total_excise_duty_vat);
        $total->setBaseGrandTotal($total->getBaseGrandTotal()+$total_excise_duty_vat);

        return $this;
    }


    /**
    * Get getExciseDutyVat
    *
    * @return \Magento\Framework\Phrase
    */
    public function getExciseDutyVat()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $store_id = $storeManager->getStore()->getId();

        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');

        // get array of all items what can be display directly
        // $items = $cart->getQuote()->getAllVisibleItems();

        // retrieve quote items array
        $items = $cart->getQuote()->getAllItems();

        $total_excise_duty_vat = 0;
        foreach($items as $item) {
            $product_id = $item->getProductId();
            $product_sku = $item->getSku();
            $product_qty = $item->getQty();
            $product_price = $item->getPrice();

            $productModel = $objectManager->create('Magento\Catalog\Model\Product');
            $product = $productModel->setStoreId($store_id)->load($product_id);
            $product_id = $product->getId();
            $product_excise_duty = $product->getExciseDuty();
            $product_excise_duty_price = $product->getExciseDutyPrice();
            $product_price = $product_excise_duty_price;

            if ($product_excise_duty == true) {
                // $vat_percent_for_excise_duty = 10;
                $vat_percent_for_excise_duty = $this->getExciseDutyVatPercent($product_id, $store_id);

                $item_total_price = $product_price * $product_qty;
                $item_excise_duty_vat = (($item_total_price * $vat_percent_for_excise_duty) / 100);
                $total_excise_duty_vat += $item_excise_duty_vat;
            }
        }
        if (!is_numeric($total_excise_duty_vat)) {
            $total_excise_duty_vat = 0;
        }

        return $total_excise_duty_vat;
    }

    /**
    * Get getExciseDutyVatPercent
    *
    * @return \Magento\Framework\Phrase
    */
    public function getExciseDutyVatPercent($product_id, $store_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $store_id = $storeManager->getStore()->getId();
        $taxCalculation = $objectManager->get(\Magento\Tax\Model\Calculation::class);
        $productModel = $objectManager->create('Magento\Catalog\Model\Product');

        $vat_percent_for_excise_duty = 0;
        if (!empty($product_id) && !empty($store_id)) {
            $product = $productModel->setStoreId($store_id)->load($product_id);
            $product_id = $product->getId();
            $product_tax_class_id = $product->getTaxClassId();

            // $defaultCustomerTaxClassId = $this->scopeConfig->getValue('tax/classes/default_customer_tax_class');
            $store = $storeManager->getStore(); 
            $request = $taxCalculation->getRateRequest(null, null, null, $store);
            $tax_rate = $taxCalculation->getRate($request->setProductClassId($product_tax_class_id));

            $vat_percent_for_excise_duty = $tax_rate;
        }

        if (!is_numeric($vat_percent_for_excise_duty) || empty($vat_percent_for_excise_duty)) {
            $vat_percent_for_excise_duty = 0;
        }

        return $vat_percent_for_excise_duty;
    }

}