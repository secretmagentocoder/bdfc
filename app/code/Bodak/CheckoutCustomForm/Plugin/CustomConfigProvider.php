<?php

namespace Bodak\CheckoutCustomForm\Plugin;

use Magento\Framework\View\LayoutInterface;

class CustomConfigProvider
{

    public function __construct
    (
        LayoutInterface $layout
    )
    {
        $this->layout = $layout;
    }

    public function afterGetConfig(\Magento\Checkout\Model\Cart\CheckoutSummaryConfigProvider $subject ,$result)
    {
        $result['custom_data_checkout'] = $this->layout->createBlock('Bodak\CheckoutCustomForm\Block\Checkout\CustomCategory')->toHtml();
        return $result;
    }
}
