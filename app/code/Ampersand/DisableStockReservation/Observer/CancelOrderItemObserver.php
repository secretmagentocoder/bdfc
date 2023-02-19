<?php
declare(strict_types=1);

namespace Ampersand\DisableStockReservation\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\InventorySales\Model\GetItemsToCancelFromOrderItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Ampersand\DisableStockReservation\Service\ExecuteSourceDeductionForItems;

class CancelOrderItemObserver implements ObserverInterface
{
    /**
     * @var GetItemsToCancelFromOrderItem
     */
    private $getItemsToCancelFromOrderItem;

    /**
     * @var ExecuteSourceDeductionForItems
     */
    private $executeSourceDeductionForItems;

    /**
     * CancelOrderItemObserver constructor.
     * @param GetItemsToCancelFromOrderItem $getItemsToCancelFromOrderItem
     * @param ExecuteSourceDeductionForItems $executeSourceDeductionForItems
     */
    public function __construct(
        GetItemsToCancelFromOrderItem $getItemsToCancelFromOrderItem,
        ExecuteSourceDeductionForItems $executeSourceDeductionForItems
    ) {
        $this->getItemsToCancelFromOrderItem = $getItemsToCancelFromOrderItem;
        $this->executeSourceDeductionForItems = $executeSourceDeductionForItems;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer): void
    {
        /** @var OrderItem $orderItem */
        $orderItem = $observer->getEvent()->getItem();
        if (!$orderItem instanceof OrderItem) {
            return;
        }

        $itemsToCancel = $this->getItemsToCancelFromOrderItem->execute($orderItem);

        if (empty($itemsToCancel)) {
            return;
        }

        $this->executeSourceDeductionForItems->executeSourceDeductionForItems($orderItem, $itemsToCancel);
    }
}
