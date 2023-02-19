<?php

namespace Amasty\Orderattr\Block\Order\Plugin;

class OrderView
{
    /**
     * @param \Magento\Sales\Block\Order\Info $subject
     * @param                                 $result
     *
     * @return string
     */
    public function afterToHtml(\Magento\Sales\Block\Order\Info $subject, $result)
    {
        /** @var \Amasty\Orderattr\Block\Order\Attributes $attributesBlock */
        if ($attributesBlock = $subject->getChildBlock('order_attributes')) {
            $result .= $attributesBlock->toHtml();
        }

        return $result;
    }
}
