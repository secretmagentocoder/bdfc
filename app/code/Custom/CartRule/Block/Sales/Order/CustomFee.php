<?php
namespace Custom\CartRule\Block\Sales\Order;

class CustomFee extends \Magento\Framework\View\Element\Template
{
    /**
    * Tax configuration model
    *
    * @var \Magento\Tax\Model\Config
    */
    protected $_config;
    /**
    * @var Order
    */
    protected $_order;
    /**
    * @var \Magento\Framework\DataObject
    */
    protected $_source;

    /**
    * @param \Magento\Framework\View\Element\Template\Context $context
    * @param \Magento\Tax\Model\Config $taxConfig
    * @param array $data
    */
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    \Magento\Tax\Model\Config $taxConfig,
    array $data = []
    ) {
        $this->_config = $taxConfig;
        parent::__construct($context, $data);
    }

    /**
    * Check if we nedd display full tax total info
    *
    * @return bool
    */
    public function displayFullSummary()
    {
        return true;
    }

    /**
    * Get data (totals) source model
    *
    * @return \Magento\Framework\DataObject
    */
    public function getSource()
    {
        return $this->_source;
    } 

    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
    * @return Order
    */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
    * @return array
    */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
    * @return array
    */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
    * Initialize all order totals relates with tax
    *
    * @return \Magento\Tax\Block\Sales\Order\Tax
    */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $store = $this->getStore();
        $customfee = new \Magento\Framework\DataObject(
            [
                'code'=>'customfee',
                'strong'=>false,
                'value'=>10,
                'label'=>__('Custom Duty'),
            ]
        );
        $parent->addTotal($customfee, 'fee');
        return $this;
    }
}