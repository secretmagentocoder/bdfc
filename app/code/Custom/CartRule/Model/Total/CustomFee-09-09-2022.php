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
            'title'=>'Handling Charges',
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
        return __('Handling Charges');
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

        $arrival_quantity_on_hand = $cart->getQuote()->getArrivalQuantityOnHand();
        $arrival_quantity_on_hand_arr = json_decode($arrival_quantity_on_hand);

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
            $product_final_price = $product->getPriceInfo()->getPrice('final_price')->getValue();
            $product_excise_duty = $product->getExciseDuty();
            $product_excise_duty_price = $product->getExciseDutyPrice();
            // $product_price = $product_final_price;
            if ($product_excise_duty == true && is_numeric($product_excise_duty_price)) {
                $product_price = $product_final_price - $product_excise_duty_price;
            }else{
                $product_price = $product_final_price;
            }

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

        // getCustomArraySort
        $custom_duty_arr = $this->getCustomArraySort($custom_duty_arr);

        // getCustomOnHandQtyArraySort
        $custom_duty_arr = $this->getCustomOnHandQtyArraySort($custom_duty_arr, $arrival_quantity_on_hand_arr);

        // getCustomDutyPrice
        $total_custom_fee = $this->getCustomDutyPrice($custom_duty_arr);

        // total_custom_fee
        return $total_custom_fee;
    }

    /**
    * Get getCustomArraySort
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomArraySort($custom_duty_arr)
    {
        foreach ($custom_duty_arr as $key => $value) {
            $custom_duty_arr_ = $value;

            usort($custom_duty_arr_, function($a, $b) {
                $retval = $a['product_price_per_uom'] <=> $b['product_price_per_uom'];
                return $retval;
            });

            /*usort($custom_duty_arr_, function($a, $b) {
                $retval = $a['order'] <=> $b['order'];
                if ($retval == 0) {
                    $retval = $a['suborder'] <=> $b['suborder'];
                    if ($retval == 0) {
                        $retval = $a['details']['subsuborder'] <=> $b['details']['subsuborder'];
                    }
                }
                return $retval;
            });*/

            $custom_duty_arr[$key] = $custom_duty_arr_;
        }

        return $custom_duty_arr;
    }

    /**
    * Get getCustomParentArraySort
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomParentArraySort($parent_category_arr)
    {
        $custom_parent_category_arr = [];

        if (!empty($parent_category_arr)) {
            foreach ($parent_category_arr as $key => $parent_category_items) {
                $custom_parent_category_code = $key;
                $custom_duty_items_count = 0;
                foreach ($parent_category_items as $key => $custom_duty_items) {
                    $custom_allowence_category_id = $key;
                    $custom_qty_per_uom_total = 0;
                    if (isset($custom_duty_items['count'])) {
                        // code...
                        $custom_duty_items_count = $custom_duty_items['count'];
                        $custom_parent_category_arr [$custom_parent_category_code]['count']= $custom_duty_items_count;
                    }else{
                        // 
                        foreach ($custom_duty_items as $key => $value) {
                            $product_qty = $value['product_qty'];
                            $product_price = $value['product_price'];
                            $product_qty_per_uom = $value['product_qty_per_uom'];
                            $product_price_per_uom = $value['product_price_per_uom'];
                            
                            $value ['category_code']= $custom_allowence_category_id;
                            $custom_parent_category_arr [$custom_parent_category_code][]= $value;
                        }
                    }
                }

            }
        }

        // custom_parent_category_arr
        foreach ($custom_parent_category_arr as $key => $value) {
            $custom_parent_category_items_ = $value;
            if (!isset($value['count'])) {
                usort($custom_parent_category_items_, function($a, $b) {
                    $retval = $a['product_price_per_uom'] <=> $b['product_price_per_uom'];
                    return $retval;
                });

                $custom_parent_category_arr[$key] = $custom_parent_category_items_;
            }
        }

        return $custom_parent_category_arr;
    }

    /**
    * Get getCustomOnHandQtyArraySort
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomOnHandQtyArraySort($custom_duty_arr, $arrival_quantity_on_hand_arr)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');

        if (!empty($custom_duty_arr)) {
            if (!empty($arrival_quantity_on_hand_arr)) {
                $on_hand_qty_spirit_wine = $arrival_quantity_on_hand_arr[0]->on_hand_qty_spirit_wine;
                $on_hand_qty_beer = $arrival_quantity_on_hand_arr[0]->on_hand_qty_beer;
                $on_hand_qty_tobacco = $arrival_quantity_on_hand_arr[0]->on_hand_qty_tobacco;
                $on_hand_qty_flv_tobacco = $arrival_quantity_on_hand_arr[0]->on_hand_qty_flv_tobacco;

                // on_hand_qty_beer
                $category_code = 'BEER';
                $query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('category_code = ?', $category_code);
                $results = $connection->fetchRow($query);
                if (!empty($results)) {
                    $category_id = $results['id'];

                    if (!empty($on_hand_qty_beer) && $on_hand_qty_beer != 0 && isset($custom_duty_arr[$category_id])) {
                        $product_qty_per_uom = $custom_duty_arr[$category_id][0]['product_qty_per_uom'];
                        $product_qty_per_uom_ = $product_qty_per_uom + $on_hand_qty_beer;
                        $custom_duty_arr [$category_id][0]['product_qty_per_uom']= $product_qty_per_uom_;
                    }
                }
                // on_hand_qty_tobacco
                $category_code = 'TOBACCO';
                $query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('category_code = ?', $category_code);
                $results = $connection->fetchRow($query);
                if (!empty($results)) {
                    $category_id = $results['id'];

                    if (!empty($on_hand_qty_beer) && $on_hand_qty_beer != 0 && isset($custom_duty_arr[$category_id])) {
                        $product_qty_per_uom = $custom_duty_arr[$category_id][0]['product_qty_per_uom'];
                        $product_qty_per_uom_ = $product_qty_per_uom + $on_hand_qty_beer;
                        $custom_duty_arr [$category_id][0]['product_qty_per_uom']= $product_qty_per_uom_;
                    }
                }
                // on_hand_qty_flv_tobacco
                $category_code = 'FLV TOBACCO';
                $query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('category_code = ?', $category_code);
                $results = $connection->fetchRow($query);
                if (!empty($results)) {
                    $category_id = $results['id'];

                    if (!empty($on_hand_qty_beer) && $on_hand_qty_beer != 0 && isset($custom_duty_arr[$category_id])) {
                        $product_qty_per_uom = $custom_duty_arr[$category_id][0]['product_qty_per_uom'];
                        $product_qty_per_uom_ = $product_qty_per_uom + $on_hand_qty_beer;
                        $custom_duty_arr [$category_id][0]['product_qty_per_uom']= $product_qty_per_uom_;
                    }
                }
            }
        }

        return $custom_duty_arr;
    }

    /**
    * Get getCustomParentOnHandQtyArraySort
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomParentOnHandQtyArraySort($custom_parent_category_arr, $arrival_quantity_on_hand_arr)
    {
        if (!empty($custom_parent_category_arr)) {
            if (!empty($arrival_quantity_on_hand_arr)) {
                $on_hand_qty_spirit_wine = $arrival_quantity_on_hand_arr[0]->on_hand_qty_spirit_wine;
                $on_hand_qty_beer = $arrival_quantity_on_hand_arr[0]->on_hand_qty_beer;
                $on_hand_qty_tobacco = $arrival_quantity_on_hand_arr[0]->on_hand_qty_tobacco;
                $on_hand_qty_flv_tobacco = $arrival_quantity_on_hand_arr[0]->on_hand_qty_flv_tobacco;

                // on_hand_qty_spirit_wine
                if (!empty($on_hand_qty_spirit_wine) && $on_hand_qty_spirit_wine != 0 && isset($custom_parent_category_arr['SPIRIT+WINE'])) {
                    if(isset($custom_parent_category_arr['SPIRIT+WINE'][0])){
                        $product_qty_per_uom = $custom_parent_category_arr['SPIRIT+WINE'][0]['product_qty_per_uom'];
                        $product_qty_per_uom_ = $product_qty_per_uom + $on_hand_qty_spirit_wine;
                        $custom_parent_category_arr['SPIRIT+WINE'][0]['product_qty_per_uom'] = $product_qty_per_uom_;
                    }
                   
                }
            }
        }

        return $custom_parent_category_arr;
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
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
        
        $arrival_quantity_on_hand = $cart->getQuote()->getArrivalQuantityOnHand();
        $arrival_quantity_on_hand_arr = json_decode($arrival_quantity_on_hand);

        $parent_category_arr = [];
        $total_custom_duty_fee = 0;
        if (!empty($custom_duty_arr)) {
            foreach ($custom_duty_arr as $key => $custom_duty_items) {
                $custom_allowence_category_id = $key;
                $product_qty_per_uom_total = 0;
                foreach ($custom_duty_items as $key => $value) {
                    $product_qty = $value['product_qty'];
                    $product_price = $value['product_price'];
                    $product_qty_per_uom = $value['product_qty_per_uom'];
                    $product_price_per_uom = $value['product_price_per_uom'];
                    $product_qty_per_uom_total += $product_qty_per_uom;
                }

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
                        $custom_duty_qty_per_uom_total = $product_qty_per_uom_total_;

                        $item_price_per_uom_total = 0;
                        foreach ($custom_duty_items as $key => $value) {
                            $product_qty = $value['product_qty'];
                            $product_price = $value['product_price'];
                            $product_qty_per_uom = $value['product_qty_per_uom'];
                            $product_price_per_uom = $value['product_price_per_uom'];

                            if ($custom_duty_qty_per_uom_total > 0) {
                                $item_price_per_uom = 0;
                                if ($custom_duty_qty_per_uom_total < $product_qty_per_uom) {
                                    $item_price_per_uom = $product_price_per_uom * $custom_duty_qty_per_uom_total;
                                }else{
                                    $item_price_per_uom = $product_price_per_uom * $product_qty_per_uom;
                                }
                                $item_price_per_uom_total += $item_price_per_uom;
                                $custom_duty_qty_per_uom_total = $custom_duty_qty_per_uom_total - $product_qty_per_uom;
                            }else{
                                break;
                            }
                        }

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

        // getCustomParentArraySort
        $custom_parent_category_arr = $this->getCustomParentArraySort($parent_category_arr);

        // getCustomParentOnHandQtyArraySort
        $custom_parent_category_arr = $this->getCustomParentOnHandQtyArraySort($custom_parent_category_arr, $arrival_quantity_on_hand_arr);

        // getCustomParentDutyPrice
        $total_custom_fee = $this->getCustomParentDutyPrice($custom_parent_category_arr);

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
        $parent_allowence_category_code = '';
        if (!empty($parent_category_arr)) {
            foreach ($parent_category_arr as $key => $parent_category_items) {
                $parent_allowence_category_code = $key;
                $total_custom_parent_fee = 0;
                $parent_qty_per_uom_total = 0;
                $parent_duty_items_count = 0;
                $parent_duty_qty_per_uom_total = 0;

                if (isset($parent_category_items['count'])) {
                    // code...
                    $parent_duty_items_count += $parent_category_items['count'];
                }
                // 
                foreach ($parent_category_items as $key => $value) {
                    if (is_numeric($key)) {
                        $product_qty = $value['product_qty'];
                        $product_price = $value['product_price'];
                        $product_qty_per_uom = $value['product_qty_per_uom'];
                        $product_price_per_uom = $value['product_price_per_uom'];
                        $parent_qty_per_uom_total += $product_qty_per_uom;

                    }
                }

                if (!empty($parent_allowence_category_code)) {
                    // web_custom_allowence_category
                    $query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('parent_category_name = ?', $parent_allowence_category_code);
                    $results = $connection->fetchRow($query);
                    // print_r($results);

                    $item_total_price_uom = 0;
                    if (!empty($results)) {
                        $custom_calculation_type = $results['custom_calculation_type'];
                        $custom_charge_amount = $results['custom_charge_amount'];
                        $parent_category_name = $results['parent_category_name'];
                        $parent_category_limit_quanity = $results['parent_category_limit_quanity'];

                        if ($parent_qty_per_uom_total > $parent_category_limit_quanity) {
                            $parent_qty_per_uom_total_ = $parent_qty_per_uom_total - $parent_category_limit_quanity;
                            $parent_duty_qty_per_uom_total = $parent_qty_per_uom_total_;

                            $item_price_per_uom_total = 0;
                            foreach ($parent_category_items as $key => $value) {
                                $product_qty = $value['product_qty'];
                                $product_price = $value['product_price'];
                                $product_qty_per_uom = $value['product_qty_per_uom'];
                                $product_price_per_uom = $value['product_price_per_uom'];
                                $category_code = $value['category_code'];

                                if ($parent_duty_qty_per_uom_total > 0) {
                                    $item_price_per_uom = 0;
                                    if ($parent_duty_qty_per_uom_total < $product_qty_per_uom) {
                                        $item_price_per_uom = $product_price_per_uom * $parent_duty_qty_per_uom_total;
                                    }else{
                                        $item_price_per_uom = $product_price_per_uom * $product_qty_per_uom;
                                    }
                                    $parent_duty_qty_per_uom_total = $parent_duty_qty_per_uom_total - $product_qty_per_uom;

                                    // web_custom_allowence_category
                                    $custom_query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('category_code = ?', $category_code);
                                    $custom_results = $connection->fetchRow($custom_query);
                                    // print_r($custom_results);
                                    if (!empty($custom_results)) {
                                        $custom_calculation_type = $custom_results['custom_calculation_type'];
                                        $custom_charge_amount = $custom_results['custom_charge_amount'];

                                        if ($custom_calculation_type == '%') {
                                            $item_total_price_uom = (($item_price_per_uom * $custom_charge_amount) / 100);
                                        }elseif ($custom_calculation_type == 'Amount') {
                                            $item_total_price_uom = $custom_charge_amount;
                                        }
                                    }

                                    $total_custom_parent_fee += $item_total_price_uom;
                                }else{
                                    break;
                                }
                            }
                        }
                        if ($parent_duty_items_count > 0) {
                            $parent_qty_per_uom_total_ = $parent_qty_per_uom_total;
                            $parent_duty_qty_per_uom_total = $parent_qty_per_uom_total_;

                            $item_price_per_uom_total = 0;
                            foreach ($parent_category_items as $key => $value) {
                                if (is_numeric($key)) {
                                    $product_qty = $value['product_qty'];
                                    $product_price = $value['product_price'];
                                    $product_qty_per_uom = $value['product_qty_per_uom'];
                                    $product_price_per_uom = $value['product_price_per_uom'];
                                    $category_code = $value['category_code'];

                                    if ($parent_duty_qty_per_uom_total > 0) {
                                        $item_price_per_uom = 0;
                                        if ($parent_duty_qty_per_uom_total < $product_qty_per_uom) {
                                            $item_price_per_uom = $product_price_per_uom * $parent_duty_qty_per_uom_total;
                                        }else{
                                            $item_price_per_uom = $product_price_per_uom * $product_qty_per_uom;
                                        }
                                        $parent_duty_qty_per_uom_total = $parent_duty_qty_per_uom_total - $product_qty_per_uom;

                                        // web_custom_allowence_category
                                        $custom_query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('category_code = ?', $category_code);
                                        $custom_results = $connection->fetchRow($custom_query);
                                        // print_r($custom_results);
                                        if (!empty($custom_results)) {
                                            $custom_calculation_type = $custom_results['custom_calculation_type'];
                                            $custom_charge_amount = $custom_results['custom_charge_amount'];

                                            if ($custom_calculation_type == '%') {
                                                $item_total_price_uom = (($item_price_per_uom * $custom_charge_amount) / 100);
                                            }elseif ($custom_calculation_type == 'Amount') {
                                                $item_total_price_uom = $custom_charge_amount;
                                            }
                                        }

                                        $total_custom_parent_fee += $item_total_price_uom;
                                    }else{
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    $total_custom_duty_fee += $total_custom_parent_fee;
                }
            }
        }
        
        // $total_custom_duty_fee = round($total_custom_duty_fee, '3');

        return $total_custom_duty_fee;
    }

} 