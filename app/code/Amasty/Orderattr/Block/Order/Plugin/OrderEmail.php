<?php

namespace Amasty\Orderattr\Block\Order\Plugin;

class OrderEmail
{
    /**
     * @param \Magento\Sales\Block\Items\AbstractItems $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(\Magento\Sales\Block\Items\AbstractItems $subject, $result)
    {
        /** @var \Amasty\Orderattr\Block\Order\Attributes $attributesBlock */
        if ($attributesBlock = $subject->getChildBlock('order_attributes')) {
            $result .= $attributesBlock->toHtml();
        }

        return $result;
    }
}
