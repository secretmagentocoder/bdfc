<?php
namespace Custom\CartRule\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class CustomFee extends AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {      
        $amount = $creditmemo->getOrder()->getHandlingCharges();
        $creditmemo->setCustomfee($amount);
        $amount += $creditmemo->getOrder()->getHandlingChargesTax();


        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $amount);

        return $this;
    }

}
