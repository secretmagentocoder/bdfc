<?php
/**
 * @package   Bodak\CheckoutCustomForm
 * @author    Slawomir Bodak <slawek.bodak@gmail.com>
 * @copyright © 2017 Slawomir Bodak
 * @license   See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Bodak\CheckoutCustomForm\Model;

use Magento\Quote\Model\QuoteIdMaskFactory;
use Bodak\CheckoutCustomForm\Api\DepartureFieldsGuestRepositoryInterface;
use Bodak\CheckoutCustomForm\Api\DepartureFieldsRepositoryInterface;
use Bodak\CheckoutCustomForm\Api\Data\DepartureFieldsInterface;

/**
 * Class DepartureFieldsGuestRepository
 *
 * @category Model/Repository
 * @package  Bodak\CheckoutCustomForm\Model
 */
class DepartureFieldsGuestRepository implements DepartureFieldsGuestRepositoryInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var DepartureFieldsRepositoryInterface
     */
    protected $departureFieldsRepository;

    /**
     * @param QuoteIdMaskFactory              $quoteIdMaskFactory
     * @param DepartureFieldsRepositoryInterface $departureFieldsRepository
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        DepartureFieldsRepositoryInterface $departureFieldsRepository
    ) {
        $this->quoteIdMaskFactory     = $quoteIdMaskFactory;
        $this->departureFieldsRepository = $departureFieldsRepository;
    }

    /**
     * @param string                $cartId
     * @param DepartureFieldsInterface $departureFields
     * @return DepartureFieldsInterface
     */
    public function saveDepartureFields(
        string $cartId,
        DepartureFieldsInterface $departureFields
    ): DepartureFieldsInterface {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->departureFieldsRepository->saveDepartureFields((int)$quoteIdMask->getQuoteId(), $departureFields);
    }
}
