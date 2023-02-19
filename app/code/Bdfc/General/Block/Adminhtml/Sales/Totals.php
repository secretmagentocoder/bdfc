<?php
namespace Bdfc\General\Block\Adminhtml\Sales;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Directory\Model\Currency;

/**
 * Adminhtml sales totals block
 */
class Totals extends Template
{
    /**
     * @var Currency
     */
    protected $currency;

    public function __construct(
        Context $context,
        Currency $currency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->currency = $currency;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->currency->getCurrencySymbol();
    }

    /**
     *
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getOrder();
        $this->getSource();
        
        $handlingFee = $this->getOrder()->getHandlingCharges();
        $baseHandlingFee = $this->getOrder()->getBaseHandlingCharges();

        $total = 0;
        $total = new \Magento\Framework\DataObject(
            [
                'code' => 'customfee',
                'value' => $handlingFee,
                'base_value' => $baseHandlingFee,
                'label' => 'Handling Charges',
            ]
          );
         $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        
        return $this;
    }
}
