<?php

namespace Amasty\Orderattr\Api;

/**
 * @api
 */
interface GuestCheckoutDataRepositoryInterface
{
    /**
     * Save Data from Frontend Checkout
     *
     * @param string|int $amastyCartId
     * @param string $checkoutFormCode
     * @param string $shippingMethodCode
     * @param \Amasty\Orderattr\Api\Data\EntityDataInterface $entityData
     * @throws \Magento\Framework\Exception\InputException
     *
     * @return \Amasty\Orderattr\Api\Data\EntityDataInterface
     */
    public function save(
        $amastyCartId,
        $checkoutFormCode,
        $shippingMethodCode,
        \Amasty\Orderattr\Api\Data\EntityDataInterface $entityData
    );
}
