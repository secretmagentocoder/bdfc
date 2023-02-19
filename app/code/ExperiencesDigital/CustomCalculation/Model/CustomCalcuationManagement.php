<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace ExperiencesDigital\CustomCalculation\Model;

use ExperiencesDigital\CustomCalculation\Api\CustomCalcuationManagementInterface;

class CustomCalcuationManagement implements CustomCalcuationManagementInterface
{
    protected $objectManager;
    protected $resourceConnection;

    protected $customCats = [];
    protected $onhandQtyArray = [];
    protected $calculatedCustoms = [];
    protected $calculatedCustomAmountsWithoutVAT = [];
    protected $leastCustomDuty;
    protected $cartItems = [];
    protected $cartId = 0;
    protected $cartItemCalculationsRatio = [];

    public function __construct(
        \Custom\CartRule\Helper\Data $helperApi,
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    )
    {
        $this->helperApi = $helperApi;
        $this->quoteRepository = $quoteRepository;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->resourceConnection = $this->objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
    }

    /**
     * Custom Duty Calculation based on JSON Data.
     *
     * @api
     * @param string[] $nums The array of numbers to sum.
     * @return float amount.
     */
    public function postCustom_calcuation($cart)
    {
        $customAmount = 0.000;

        if (is_array($cart) && count($cart) > 0) {
            if (isset($cart['cart_id'])) {
                $this->cartId = $cart['cart_id'];
            }
            if (isset($cart['onhand_qty'])) {
                $this->onhandQtyArray = $cart['onhand_qty'];
            }
            if (isset($cart['items'])) {

                $this->cartItems = $cart['items'];
                $this->calculateCartItemsRatio();

                $customAmount =  (float)$this->getLeastCustomDuty();
            }
        }
        if (isset($cart['debug']) && $cart['debug'] == 1) {
            return 0;
        }
        return $customAmount;
    }

    protected function getLeastCustomDuty()
    {
        $totalCustomDuty = 0;

        if (isset($this->calculatedCustomAmountsWithoutVAT['SPIRIT+WINE']) && $this->calculatedCustomAmountsWithoutVAT['SPIRIT+WINE'] > 0) {
            unset($this->calculatedCustomAmountsWithoutVAT['SPIRIT']);
            unset($this->calculatedCustomAmountsWithoutVAT['WINE']);
        }

        foreach ($this->calculatedCustomAmountsWithoutVAT as $amount) {
            if ($amount > 0) {
                $totalCustomDuty = $totalCustomDuty + $amount;
            }
        }

        return $totalCustomDuty;
    }

    private function calculateCartItemsRatio()
    {
        $items = [];
        $cartItems = $this->cartItems;
        $productRepository = $this->objectManager->get('\Magento\Catalog\Model\ProductRepository');

        foreach ($cartItems as $item) {
            $customChargeAmount = 100;
            $product = $productRepository->get($item['sku']);
            $customAllowanceItemCat = $product->getData('custom_allowence_category');

            $qtyPerCustomUOM = $product->getData('qty_per_custom_uom'); //
            $price = (float)$product->getData('unit_price');
            $catData = $this->getCategoryLimits($customAllowanceItemCat);
            if (isset($catData['Custom_Charge_Amount'])) {
                $customChargeAmount = $catData['Custom_Charge_Amount'];
            }
            if ($qtyPerCustomUOM > 0) {
                $calculatedRatio = (($price / $qtyPerCustomUOM) *  $customChargeAmount) / 100;
            } else {
                $calculatedRatio = 0;
            }

            $items[$item['sku']] = ['calculated_ratio' => $calculatedRatio, 'custom_cat' => $customAllowanceItemCat, 'unit_price' => $price, 'per_uom_qty' => $qtyPerCustomUOM, 'sku' => $item['sku'], 'qty' => $item['qty'], 'total_qty' => $qtyPerCustomUOM * $item['qty']];
        }

        $calculated_ratio = array_column($items, 'calculated_ratio');

        array_multisort($calculated_ratio, SORT_ASC, $items);
        $this->cartItemCalculationsRatio = $items;
        $this->setCartCustomCats();

        foreach ($this->cartItemCalculationsRatio as $key => $value) {
            $temp = $value;
            unset($this->cartItemCalculationsRatio[$key]);
            $this->cartItemCalculationsRatio[$value['sku']] = $value;
        }
        $this->processCustomCatsAllowanceLimits();
        //var_dump($this->cartItemCalculationsRatio);
       // var_dump($this->customCats);
        //var_dump($this->calculatedCustomAmountsWithoutVAT);

    }

    private function setCartCustomCats()
    {
        $cartItems = $this->cartItemCalculationsRatio;
        $customCat = '';
        $spiritWine=0;

        foreach ($cartItems as $item) {
            if ($item['custom_cat'] != $customCat) {
                $customCat = $item['custom_cat'];
                $this->customCats[$customCat] = $this->getCartItemTotalQty($customCat);
            }
        }
        if(isset($this->customCats['WINE'])){
            $spiritWine+= $this->customCats['WINE'];
        }
        if (isset($this->customCats['SPIRIT'])) {
            $spiritWine += $this->customCats['SPIRIT'];
        }        
        $this->customCats['SPIRIT+WINE'] = $spiritWine ;        
    }

    private function processCustomCatsAllowanceLimits()
    {
        $customCats = $this->customCats;
        if ($this->cartId) {
            $quote = $this->quoteRepository->get($this->cartId);
        }
       
        $catOnHandLimit = 0;
        foreach ($customCats as $cat => $limit) {
            if (isset($this->onhandQtyArray[$cat])) {
                $catOnHandLimit = $this->onhandQtyArray[$cat];
            }

            $catCustomAmount = 0;
            if ($cat == 'SPIRIT+WINE') { //Parent Categfory
                $maxCatLimit = 3;
                $catArray = ['SPIRIT', 'WINE'];
            } else {
                $maxCatLimit = $this->getCustomCatMaxLimit($cat);
                $catArray = [$cat];
            }


            if ($catOnHandLimit >= $maxCatLimit) {
                $catOnHandLimit = $maxCatLimit;
            }
            $exceeLimit = ($limit + $catOnHandLimit) - $maxCatLimit;
            if ($exceeLimit > 0) {
                foreach ($this->cartItemCalculationsRatio as $sku => $item) {
                   
                    if (in_array($item['custom_cat'], $catArray)) {
                        $totalCartQty = $this->cartItemCalculationsRatio[$sku]['total_qty'];
                       

                        if ($totalCartQty >= $exceeLimit) {
                            $catCustomAmount = $catCustomAmount + $exceeLimit * $this->cartItemCalculationsRatio[$sku]['calculated_ratio'];
                            $catCustomAmountPerItem = $exceeLimit * $this->cartItemCalculationsRatio[$sku]['calculated_ratio'];
                            $exceeLimit = 0;
                            
                            if ($this->cartId) {
                                foreach ($quote->getAllVisibleItems() as $cartItem) {
                                    if ($cartItem->getSku() == $sku) {
                                        $handLingTax = (float) $catCustomAmountPerItem * ($this->helperApi->getCustomDuty()/100);
                                        $handlingChargesCurrent = $this->helperApi->convertPriceFromBaseCurrencyToCurrentCurrency($catCustomAmountPerItem);
                                        $handLingTaxCurrent = $this->helperApi->convertPriceFromBaseCurrencyToCurrentCurrency($handLingTax);
                                        $customDutyPerItem = $this->cartItemCalculationsRatio[$sku]['calculated_ratio'];
    
                                        $cartItem->setData('handling_charges', $handlingChargesCurrent);
                                        $cartItem->setData('base_handling_charges', $catCustomAmountPerItem);
                                        $cartItem->setData('handling_charges_tax', $handLingTaxCurrent);
                                        $cartItem->setData('base_handling_charges_tax', $handLingTax);
                                        $cartItem->setData('base_custom_duty_per_item', $customDutyPerItem);
                                        $cartItem->setData('custom_considered_qty', $exceeLimit);
                                        $cartItem->save();
                                        break;
                                    }                                
                                }
                            }                            
                            break;
                        } else {
                            $catCustomAmount = $catCustomAmount + $totalCartQty * $this->cartItemCalculationsRatio[$sku]['calculated_ratio'];
                            $catCustomAmountPerItem = $totalCartQty * $this->cartItemCalculationsRatio[$sku]['calculated_ratio'];
                            $exceeLimit = $exceeLimit - $totalCartQty;
                            if ($this->cartId) {
                                foreach ($quote->getAllVisibleItems() as $cartItem) {
                                    if ($cartItem->getSku() == $sku) {
                                        $handLingTax = (float) $catCustomAmountPerItem * ($this->helperApi->getCustomDuty()/100);
                                        $handlingChargesCurrent = $this->helperApi->convertPriceFromBaseCurrencyToCurrentCurrency($catCustomAmountPerItem);
                                        $handLingTaxCurrent = $this->helperApi->convertPriceFromBaseCurrencyToCurrentCurrency($handLingTax);
                                        $customDutyPerItem = $this->cartItemCalculationsRatio[$sku]['calculated_ratio'];
    
                                        $cartItem->setData('handling_charges', $handlingChargesCurrent);
                                        $cartItem->setData('base_handling_charges', $catCustomAmountPerItem);
                                        $cartItem->setData('handling_charges_tax', $handLingTaxCurrent);
                                        $cartItem->setData('base_handling_charges_tax', $handLingTax);
                                        $cartItem->setData('base_custom_duty_per_item', $customDutyPerItem);
                                        $cartItem->setData('custom_considered_qty', $exceeLimit);
                                        $cartItem->save();
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                $this->calculatedCustomAmountsWithoutVAT[$cat] = $catCustomAmount;
            }
        }
    }


    private function getCartItemTotalQty($cat)
    {
        $catTotalQty = 0;
        $cartItems = $this->cartItems;
        $productRepository = $this->objectManager->get('\Magento\Catalog\Model\ProductRepository');

        foreach ($cartItems as $item) {
            $product = $productRepository->get($item['sku']);
            $customAllowanceItemCat = $product->getData('custom_allowence_category');
            $qtyPerCustomUOM = $product->getData('qty_per_custom_uom'); //
            if ($customAllowanceItemCat == $cat) {
                $catTotalQty = $catTotalQty + $qtyPerCustomUOM * $item['qty'];
            }
        }
        return $catTotalQty;
    }

    private function getCategoryLimits($cat)
    {
        $query = $this->resourceConnection->fetchAll("SELECT * FROM custom_category_calculation where Code='$cat'");
        return isset($query[0]) ? $query[0] : '';
    }
    private function getCustomCatMaxLimit($cat)
    {
        $maxLimit = 0;
        $query = $this->resourceConnection->fetchAll("SELECT * FROM custom_category_calculation where Code='$cat'");
        if (isset($query[0])) {
            $maxLimit = $query[0]['Limit_Quantity'];
        }
        return $maxLimit;
    }
}
