<?php

namespace Amasty\Orderattr\Model\Entity;

use Amasty\Orderattr\Api\Data\EntityDataInterface;
use Amasty\Orderattr\Api\GuestCheckoutDataRepositoryInterface;
use Amasty\Orderattr\Api\CheckoutDataRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteRepository;

class GuestCheckoutDataRepository implements GuestCheckoutDataRepositoryInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CheckoutDataRepositoryInterface
     */
    private $repository;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteRepository $quoteRepository,
        CheckoutDataRepositoryInterface $repository
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->repository = $repository;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @inheritdoc
     */
    public function save(
        $amastyCartId,
        $checkoutFormCode,
        $shippingMethodCode,
        EntityDataInterface $entityData
    ) {
        if ($parentId = $this->quoteIdMaskFactory->create()->load($amastyCartId, 'masked_id')->getQuoteId()) {
            try {
                $quote = $this->quoteRepository->get($parentId);

                return $this->repository->save($parentId, $checkoutFormCode, $shippingMethodCode, $entityData);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                throw new \Magento\Framework\Exception\InputException(__('Quote doesn\'t exist.'));
            }
        }

        throw new \Magento\Framework\Exception\InputException(__('Quote doesn\'t exist.'));
    }
}
