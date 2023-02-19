<?php
/**

@Author paygcc.com contact info@paygcc.com

 */
namespace PL\Paygcc\Block\ApiCheckout;


class Info extends \Magento\Payment\Block\Info
{
    protected $_template = 'PL_Paygcc::apicheckout/info.phtml';


    protected $paygccHelper;

    /**
     * Info constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \PL\Paygcc\Helper\Data $paygccHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \PL\Paygcc\Helper\Data $paygccHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paygccHelper = $paygccHelper;
    }
}