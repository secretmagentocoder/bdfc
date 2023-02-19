<?php
namespace Flamingo\Checkout\Block;

class Tax extends \Magento\Tax\Block\Sales\Order\Tax
{
    /**
     * Tax helper
     * @var \Magento\Tax\Helper\Data
     */
    protected $helper;

    /**
     * Tax Constructor
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $taxConfig, $data);
    }

    /**
     * Get Helper Tax
     * @return \Magento\Tax\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
