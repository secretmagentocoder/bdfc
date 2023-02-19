<?php

namespace Amasty\Orderattr\Block\Adminhtml\Order\Plugin;

class OrderView
{
    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View\Info $subject
     * @param string                                         $result
     *
     * @return string
     */
    public function afterToHtml(
        \Magento\Sales\Block\Adminhtml\Order\View\Info $subject,
        $result
    ) {
        $attributesBlock = $subject->getChildBlock('order_attributes');
        if ($attributesBlock) {
            $attributesBlock->setTemplate("Amasty_Orderattr::order/view/attributes.phtml");
            $result = $result . $attributesBlock->toHtml();
        }

        return $result;
    }
}
