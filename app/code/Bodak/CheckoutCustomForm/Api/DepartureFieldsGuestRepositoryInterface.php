<?php
/**
 * Checkout Departure fields guest repository interface
 *
 * @package   Bodak\CheckoutDepartureForm
 * @author    Slawomir Bodak <slawek.bodak@gmail.com>
 * @copyright © 2017 Slawomir Bodak
 * @license   See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Bodak\CheckoutCustomForm\Api;

use Magento\Sales\Model\Order;
use Bodak\CheckoutCustomForm\Api\Data\DepartureFieldsInterface;

/**
 * Interface DepartureFieldsGuestRepositoryInterface
 *
 * @category Api/Interface
 * @package  Bodak\CheckoutDepartureForm\Api
 */
interface DepartureFieldsGuestRepositoryInterface
{
    /**
     * Save checkout Departure fields
     *
     * @param string                                                   $cartId       Guest Cart id
     * @param \Bodak\CheckoutCustomForm\Api\Data\DepartureFieldsInterface $departureFields Departure fields
     *
     * @return \Bodak\CheckoutCustomForm\Api\Data\DepartureFieldsInterface
     */
    public function saveDepartureFields(
        string $cartId,
        DepartureFieldsInterface $departureFields
    ): DepartureFieldsInterface;
}
