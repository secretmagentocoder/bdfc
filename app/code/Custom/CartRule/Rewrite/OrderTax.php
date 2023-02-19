<?php
namespace Custom\CartRule\Rewrite;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Math\Random;

class OrderTax extends \Magento\Sales\Block\Adminhtml\Order\Totals\Tax
{
  
    /**
     * Display tax amount
     *
     * @param string $amount
     * @param string $baseAmount
     * @return string
     */
    public function displayAmount($amount, $baseAmount)
    {
        return $this->_salesAdminHelper->displayPrices(
            $this->getSource(),
            $baseAmount+$this->getOrder()->getBaseHandlingChargesTax(), 
            $amount+$this->getOrder()->getHandlingChargesTax(),
            false,
            '<br />'
        );
    }
}
