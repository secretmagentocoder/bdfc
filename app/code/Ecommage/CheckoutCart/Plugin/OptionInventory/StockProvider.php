<?php

namespace Ecommage\CheckoutCart\Plugin\OptionInventory;

/**
 * Class StockProvider
 */
class StockProvider
{

    /**
     * @param          $subject
     * @param callable $proceed
     * @param          $items
     * @param          $cart
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetRequestedData($subject, callable $proceed, $items, $cart)
    {
        $requestedData = [];
        $items = !is_array($items) ? [$items] : $items;
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            $itemRequestedData = $subject->getItemData($item, $cart);
            foreach ($itemRequestedData as $valueId => $valueData) {
                if ($this->isRaffle($item)) {
                    $valueData->setQty(1);
                }

                if (isset($requestedData[$valueId])) {
                    $value = $requestedData[$valueId];
                    $value->setQty($value->getQty() + $valueData->getQty());
                } else {
                    $requestedData[$valueId] = $valueData;
                }
            }
        }

        return $requestedData;
    }

    /**
     * @param $item
     *
     * @return bool
     */
    protected function isRaffle($item)
    {
        $product = $item->getProduct();
        $value   = $product->getData('is_check_raffle');
        if (empty($value)) {
            $item->setData('product', null);
            $product = $item->getProduct();
        }

        if ($product->getData('is_check_raffle') == 1) {
            return true;
        }

        return false;
    }
}
