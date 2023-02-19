<?php
/**
 * 
 * @package Custom_CartRule
 */
declare(strict_types=1);

namespace Custom\CartRule\Plugin\Model\Sales\Total\Quote;

use Magento\Framework\Serialize\Serializer\Json;

class Tax
{

    public function __construct
    (
        \Ecommage\CheckoutData\Helper\Data $data,
        \Custom\CartRule\Helper\Data $helperApi,
        \Magento\Tax\Model\Config $taxConfig,
        Json $serializer
    )
    {
        $this->helperApi = $helperApi;
        $this->helper = $data;
        $this->_config = $taxConfig;
        $this->serializer = $serializer;
    }

    public function aroundFetch(\Magento\Tax\Model\Sales\Total\Quote\Tax $subject,
                                callable $proceed,
                                \Magento\Quote\Model\Quote $quote,
                                \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $totals = [];
        $store = $quote->getStore();
        $applied = $total->getAppliedTaxes();
        if (is_string($applied)) {
            $applied = $this->serializer->unserialize($applied);
        }
        $amount = $total->getTaxAmount();
        if ($amount === null) {
            $this->enhanceTotalData($quote, $total);
            $amount = $total->getTaxAmount();
        }

        $taxAmount = $amount + $total->getTotalAmount('discount_tax_compensation');
        $area = null;
        if ($this->_config->displayCartTaxWithGrandTotal($store) && $total->getGrandTotal()) {
            $area = 'taxes';
        }
        $handLing = (float) $quote->getHandlingCharges() * ($this->helperApi->getCustomDuty()/100);
        $totals[] = [
            'code' => 'tax',
            'title' => __('Tax'),
            'full_info' => $applied ? $applied : [],
            'value' => $amount + $handLing,
            'area' => $area,
        ];

        /**
         * Modify subtotal
         */
        if ($this->_config->displayCartSubtotalBoth($store) || $this->_config->displayCartSubtotalInclTax($store)) {
            if ($total->getSubtotalInclTax() > 0) {
                $subtotalInclTax = $total->getSubtotalInclTax();
            } else {
                $subtotalInclTax = $total->getSubtotal() + $taxAmount - $total->getShippingTaxAmount();
            }

            $totals[] = [
                'code' => 'subtotal',
                'title' => __('Subtotal'),
                'value' => $subtotalInclTax,
                'value_incl_tax' => $subtotalInclTax,
                'value_excl_tax' => $total->getSubtotal(),
            ];
        }

        if (empty($totals)) {
            return null;
        }

        return $totals;
    }

    
    /**
     * Adds minimal tax information to the "total" data structure
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return null
     */
    protected function enhanceTotalData(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $taxAmount = 0;
        $shippingTaxAmount = 0;
        $discountTaxCompensation = 0;

        $subtotalInclTax = $total->getSubtotalInclTax();
        $computeSubtotalInclTax = true;
        if ($total->getSubtotalInclTax() > 0) {
            $computeSubtotalInclTax = false;
        }

        /** @var \Magento\Quote\Model\Quote\Address $address */
        foreach ($quote->getAllAddresses() as $address) {
            $taxAmount += $address->getTaxAmount();
            $shippingTaxAmount += $address->getShippingTaxAmount();
            $discountTaxCompensation += $address->getDiscountTaxCompensationAmount();
            if ($computeSubtotalInclTax) {
                $subtotalInclTax += $address->getSubtotalInclTax();
            }
        }

        $total->setTaxAmount($taxAmount);
        $total->setShippingTaxAmount($shippingTaxAmount);
        $total->setDiscountTaxCompensationAmount($discountTaxCompensation); // accessed via 'discount_tax_compensation'
        $total->setSubtotalInclTax($subtotalInclTax);
    }
}
