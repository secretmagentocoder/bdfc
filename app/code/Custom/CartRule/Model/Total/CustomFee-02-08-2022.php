<?php
namespace Custom\CartRule\Model\Total;

class CustomFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
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

        // $custom_fee = 100;
        $custom_fee = $this->getCustomFee();

        $balance = $custom_fee - $exist_amount;
        
        $total->setTotalAmount('customfee', $balance);
        $total->setBaseTotalAmount('customfee', $balance);
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
        // $custom_fee = 100;
        $custom_fee = $this->getCustomFee();

        return [
            'code'=>'customfee',
            'title'=>'Excise Duty',
            'value'=>$custom_fee
        ];
    }

    /**
    * Get Subtotal label
    *
    * @return \Magento\Framework\Phrase
    */
    public function getLabel()
    {
        return __('Custom Duty');
    }

    /**
    * Get getCustomFee
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomFee()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');

        $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
        // $itemsVisible = $cart->getQuote()->getAllVisibleItems();
        $items = $cart->getQuote()->getAllItems();
        
        $total_custom_fee = 0;
        $custom_duty_arr = [];
        foreach($items as $item) {
            $product_id = $item->getProductId();
            $product_sku = $item->getSku();
            $product_qty = $item->getQty();
            $product_price = $item->getPrice();

            $product = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface')->get($product_sku);
            $product_id = $product->getId();
            $custom_allowence_category = $product->getCustomAllowenceCategory();
            $qty_per_custom_uom = $product->getQtyPerCustomUom();

            // regular_price
            $product_regular_price = $product->getPriceInfo()->getPrice('regular_price')->getValue();
            $product_price = $product_regular_price;

            if (!empty($custom_allowence_category) && !empty($qty_per_custom_uom)) {
                $product_qty_per_uom = $qty_per_custom_uom * $product_qty;
                $product_price_per_uom = (1 / $qty_per_custom_uom) * $product_price;
                // $product_price_per_uom = round($product_price_per_uom, '3');

                $product_item_arr = [];
                $product_item_arr ['product_sku']= $product_sku;
                $product_item_arr ['product_price']= $product_price;
                $product_item_arr ['product_qty']= $product_qty;
                $product_item_arr ['product_qty_per_uom']= $product_qty_per_uom;
                $product_item_arr ['product_price_per_uom']= $product_price_per_uom;
                $custom_duty_arr [$custom_allowence_category][]= $product_item_arr;
            }
        }

        // getCustomDutyPrice
        $total_custom_fee = $this->getCustomDutyPrice($custom_duty_arr);

        return $total_custom_fee;
    }

    /**
    * Get getCustomDutyPrice
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomDutyPrice($custom_duty_arr)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        
        $parent_category_arr = [];
        $total_custom_duty_fee = 0;
        if (!empty($custom_duty_arr)) {
            foreach ($custom_duty_arr as $key => $custom_duty_items) {
                $custom_allowence_category_id = $key;
                $product_price_per_uom_total = 0;
                $product_qty_per_uom_total = 0;
                foreach ($custom_duty_items as $key => $value) {
                    $product_qty = $value['product_qty'];
                    $product_price = $value['product_price'];
                    $product_qty_per_uom = $value['product_qty_per_uom'];
                    $product_price_per_uom = $value['product_price_per_uom'];
                    $product_qty_per_uom_total += $product_qty_per_uom;

                    if ($product_price_per_uom < $product_price_per_uom_total || $product_price_per_uom_total == 0) {
                        $product_price_per_uom_total = $product_price_per_uom;
                    }
                }

                // web_custom_allowence_category
                $query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('id = ?', $custom_allowence_category_id);
                $results = $connection->fetchRow($query);
                // print_r($results);

                $item_total_price_uom = 0;
                if (!empty($results)) {
                    $category_code = $results['category_code'];
                    $limit_quantity = $results['limit_quantity'];
                    $custom_calculation_type = $results['custom_calculation_type'];
                    $custom_charge_amount = $results['custom_charge_amount'];
                    $parent_category_name = $results['parent_category_name'];
                    $parent_category_limit_quanity = $results['parent_category_limit_quanity'];

                    if (!empty($parent_category_name)) {
                        if ($product_qty_per_uom_total > $limit_quantity) {
                            $custom_duty_items_count = array('count' => $product_qty_per_uom_total);
                            $parent_category_arr [$parent_category_name][$category_code]= $custom_duty_items_count;
                        }else{
                            $parent_category_arr [$parent_category_name][$category_code]= $custom_duty_items;
                        }
                    }

                    if ($product_qty_per_uom_total > $limit_quantity) {
                        $product_qty_per_uom_total_ = $product_qty_per_uom_total - $limit_quantity;
                        $item_price_per_uom_total = $product_price_per_uom_total * $product_qty_per_uom_total_;
                        if ($custom_calculation_type == '%') {
                            $item_total_price_uom = (($item_price_per_uom_total * $custom_charge_amount) / 100);
                        }elseif ($custom_calculation_type == 'Amount') {
                            $item_total_price_uom = $custom_charge_amount;
                        }
                    }
                }

                $total_custom_duty_fee += $item_total_price_uom;
            }
        }

        // getCustomParentDutyPrice
        $total_custom_fee = $this->getCustomParentDutyPrice($parent_category_arr);
        $total_custom_duty_fee += $total_custom_fee;
        // $total_custom_duty_fee = round($total_custom_duty_fee, '3');

        return $total_custom_duty_fee;
    }

    /**
    * Get getCustomParentDutyPrice
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomParentDutyPrice($parent_category_arr)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        
        $total_custom_duty_fee = 0;
        if (!empty($parent_category_arr)) {
            foreach ($parent_category_arr as $key => $parent_category_items) {
                $total_custom_parent_fee = 0;
                $product_price_per_uom_total = 0;
                $parent_qty_per_uom_total = 0;
                $custom_allowence_category_code = '';
                $custom_duty_items_count = 0;

                foreach ($parent_category_items as $key => $custom_duty_items) {
                    $custom_allowence_category_id = $key;
                    $custom_qty_per_uom_total = 0;
                    if (isset($custom_duty_items['count'])) {
                        // if custom charge exist
                        $custom_duty_items_count += $custom_duty_items['count'];
                    }else{
                        // if custom charge not exist
                        foreach ($custom_duty_items as $key => $value) {
                            $product_qty = $value['product_qty'];
                            $product_price = $value['product_price'];
                            $product_qty_per_uom = $value['product_qty_per_uom'];
                            $product_price_per_uom = $value['product_price_per_uom'];
                            $custom_qty_per_uom_total += $product_qty_per_uom;

                            if ($product_price_per_uom < $product_price_per_uom_total || $product_price_per_uom_total == 0) {
                                $product_price_per_uom_total = $product_price_per_uom;
                                $custom_allowence_category_code = $custom_allowence_category_id;
                            }
                        }

                        $parent_qty_per_uom_total += $custom_qty_per_uom_total;
                    }
                }

                if (!empty($custom_allowence_category_code)) {
                    // web_custom_allowence_category
                    $query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('category_code = ?', $custom_allowence_category_code);
                    $results = $connection->fetchRow($query);
                    // print_r($results);

                    $item_total_price_uom = 0;
                    if (!empty($results)) {
                        $limit_quantity = $results['limit_quantity'];
                        $custom_calculation_type = $results['custom_calculation_type'];
                        $custom_charge_amount = $results['custom_charge_amount'];
                        $parent_category_name = $results['parent_category_name'];
                        $parent_category_limit_quanity = $results['parent_category_limit_quanity'];

                        if ($parent_qty_per_uom_total > $parent_category_limit_quanity) {
                            $parent_qty_per_uom_total_ = $parent_qty_per_uom_total - $parent_category_limit_quanity;
                            $item_price_per_uom_total = $product_price_per_uom_total * $parent_qty_per_uom_total_;
                            if ($custom_calculation_type == '%') {
                                $item_total_price_uom = (($item_price_per_uom_total * $custom_charge_amount) / 100);
                            }elseif ($custom_calculation_type == 'Amount') {
                                $item_total_price_uom = $custom_charge_amount;
                            }
                        }
                        if ($custom_duty_items_count > 0) {
                            $parent_qty_per_uom_total_ = $parent_qty_per_uom_total;
                            $item_price_per_uom_total = $product_price_per_uom_total * $parent_qty_per_uom_total_;
                            if ($custom_calculation_type == '%') {
                                $item_total_price_uom = (($item_price_per_uom_total * $custom_charge_amount) / 100);
                            }elseif ($custom_calculation_type == 'Amount') {
                                $item_total_price_uom = $custom_charge_amount;
                            }
                        }
                    }

                    $total_custom_parent_fee += $item_total_price_uom;
                }

                $total_custom_duty_fee += $total_custom_parent_fee;
            }
        }
        
        // 
        // $total_custom_duty_fee = round($total_custom_duty_fee, '3');

        return $total_custom_duty_fee;
    }

} 