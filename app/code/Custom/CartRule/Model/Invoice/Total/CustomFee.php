<?php
namespace Custom\CartRule\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class CustomFee extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        
        $amount = $invoice->getOrder()->getHandlingCharges();
        $invoice->setCustomfee($amount);
        $invoice->setHandlingCharges($amount);
        $amount += $invoice->getOrder()->getHandlingChargesTax();


        $invoice->setGrandTotal($invoice->getGrandTotal() + $amount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $amount);

        return $this;
    }

}
