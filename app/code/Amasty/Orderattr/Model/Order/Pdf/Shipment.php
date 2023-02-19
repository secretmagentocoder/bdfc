<?php

namespace Amasty\Orderattr\Model\Order\Pdf;

class Shipment extends \Magento\Sales\Model\Order\Pdf\Shipment
{
    use Traits\AbstractPdfTrait;

    /**
     * @return bool
     */
    protected function isPrintAttributesAllowed()
    {
        return (bool)$this->configProvider->isIncludeToShipmentPdf();
    }
}
