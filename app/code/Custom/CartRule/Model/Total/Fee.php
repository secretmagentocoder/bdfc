<?php
namespace Custom\CartRule\Model\Total;

class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
    * Collect grand total address amount
    * 
    * @param \Magento\Quote\Model\Quote $quote
    * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
    * @param \Magento\Quote\Model\Quote\Address\Total $total
    * @return $this
    */
    protected $quoteValidator = null;
    
    public function __construct(\Magento\Quote\Model\QuoteValidator $quoteValidator)
    {
        $this->quoteValidator = $quoteValidator;
    }
    public function collect(
    \Magento\Quote\Model\Quote $quote,
    \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
    \Magento\Quote\Model\Quote\Address\Total $total
    ){
        parent::collect($quote, $shippingAssignment, $total);

        $exist_amount = 0;

        // $excise_fee = 100;
        $excise_fee = $this->getExciseFee();

        $balance = $excise_fee - $exist_amount;
        
        $total->setTotalAmount('fee', $balance);
        $total->setBaseTotalAmount('fee', $balance);
        $total->setFee($balance);
        $total->setBaseFee($balance);
        $total->setGrandTotal($total->getGrandTotal());
        $total->setBaseGrandTotal($total->getBaseGrandTotal());

        return $this;
    }

    protected function clearValues(Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }
    
    /**
    * @param \Magento\Quote\Model\Quote $quote
    * @param Address\Total $total
    * return array|null
    */
    /**
    * Assign subtotal amount and label to address object
    * 
    * @param \Magento\Quote\Model\Quote $quote
    * @param Address\Total $total
    * @return array
    * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        // $excise_fee = 100;
        $excise_fee = $this->getExciseFee();

        return [
            'code'=>'fee',
            'title'=>'Excise Duty',
            'value'=>$excise_fee
        ];
    }

    /**
    * Get Subtotal label
    *
    * @return \Magento\Framework\Phrase
    */
    public function getLabel()
    {
        return __('Excise Duty');
    }

    /**
    * Get getExciseFee
    *
    * @return \Magento\Framework\Phrase
    */
    public function getExciseFee()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
        // $items = $cart->getQuote()->getAllVisibleItems();
        // $items = $cart->getQuote()->getAllItems();
        $session = $objectManager->get('\Magento\Checkout\Model\Session');
        $quote_repository = $objectManager->get('\Magento\Quote\Api\CartRepositoryInterface');
        $qid = $session->getQuoteId();
        if (empty($qid)) {
            return 0;
        }
        $quote = $quote_repository->get($qid);      
        $items = $quote->getAllItems();

        $total_excise_fee = 0;
        foreach($items as $item) {
            $product_id = $item->getProductId();
            $product_sku = $item->getSku();
            $product_qty = $item->getQty();
            $product_price = $item->getPrice();

            $product = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface')->get($product_sku);
            $product_id = $product->getId();
            $product_excise_duty = $product->getExciseDuty();

            if ($product_excise_duty == true) {
                // $vat_percent_for_excise_duty = 10;
                $vat_percent_for_excise_duty = $this->getExciseDutyVat();

                $item_total_price = $product_price * $product_qty;
                $item_excise_fee = (($item_total_price * $vat_percent_for_excise_duty) / 100);
                $total_excise_fee += $item_excise_fee;
            }
        }
        if (!is_numeric($total_excise_fee)) {
            $total_excise_fee = 0;
        }

        return $total_excise_fee;
    }

    /**
    * Get getExciseDutyVat
    *
    * @return \Magento\Framework\Phrase
    */
    public function getExciseDutyVat()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $currentWebsiteId = $storeManager->getStore()->getWebsiteId();

        $store_id = $currentWebsiteId;
        $income_expense_code = 2;
        $query = $connection->select()->from('web_excise_duty_setup', ['*'])->where('store_id = ?', $store_id)->where('income_expense_code = ?', $income_expense_code);
        $result = $connection->fetchRow($query);

        $vat_for_excise_duty = 0;
        if (!empty($result)) {
            $add_excise_calculation_base = $result['add_excise_calculation_base'];
            
            if ($add_excise_calculation_base == 'Retail Price') {
                $vat_for_excise_duty = 100;
            }

            if ($add_excise_calculation_base == 'Unit Cost'){
                $vat_for_excise_duty = $result['vat_for_excise_duty'];
            }
        }

        if (!is_numeric($vat_for_excise_duty) || empty($vat_for_excise_duty)) {
            $vat_for_excise_duty = 0;
        }

        return $vat_for_excise_duty;
    }

} 