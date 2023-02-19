<?php

namespace Amasty\Orderattr\Model\Order\Pdf;

class Invoice extends \Magento\Sales\Model\Order\Pdf\Invoice
{
    use Traits\AbstractPdfTrait;

    /**
     * @return bool
     */
    protected function isPrintAttributesAllowed()
    {
        return (bool)$this->configProvider->isIncludeToInvoicePdf();
    }
}
