<?php
/**
 * Checkout custom fields repository interface
 *
 * @package   Bodak\CheckoutCustomForm
 * @author    Slawomir Bodak <slawek.bodak@gmail.com>
 * @copyright © 2017 Slawomir Bodak
 * @license   See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Bodak\CheckoutCustomForm\Api;

use Magento\Sales\Model\Order;
use Bodak\CheckoutCustomForm\Api\Data\DepartureFieldsInterface;

/**
 * Interface DepartureFieldsRepositoryInterface
 *
 * @category Api/Interface
 * @package  Bodak\CheckoutDepartureForm\Api
 */
interface DepartureFieldsRepositoryInterface
{
    /**
     * Save checkout Departure fields
     *
     * @param int                                                      $cartId       Cart id
     * @param \Bodak\CheckoutCustomForm\Api\Data\DepartureFieldsInterface $departureFields Departure fields
     *
     * @return \Bodak\CheckoutCustomForm\Api\Data\DepartureFieldsInterface
     */
    public function saveDepartureFields(
        int $cartId,
        DepartureFieldsInterface $departureFields
    ): DepartureFieldsInterface;

    /**
     * Get checkoug Departure fields
     *
     * @param Order $order Order
     *
     * @return DepartureFieldsInterface
     */
    public function getDepartureFields(Order $order) : DepartureFieldsInterface;
}
