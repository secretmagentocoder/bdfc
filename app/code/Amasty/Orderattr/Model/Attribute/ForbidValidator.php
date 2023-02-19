<?php
declare(strict_types=1);

namespace Amasty\Orderattr\Model\Attribute;

use Amasty\Orderattr\Api\CheckoutAttributeRepositoryInterface;
use Magento\Quote\Model\Quote;

class ForbidValidator
{
    /**
     * @var CheckoutAttributeRepositoryInterface
     */
    private $checkoutAttributeRepository;

    public function __construct(
        CheckoutAttributeRepositoryInterface $checkoutAttributeRepository
    ) {
        $this->checkoutAttributeRepository = $checkoutAttributeRepository;
    }

    /**
     * Validate if attribute should save and not visible for current shipping method
     *
     * @param Quote $quote
     * @param string $attributeCode
     * @return bool
     */
    public function shouldDeleteAttributeValue(Quote $quote, string $attributeCode): bool
    {
        /** @var Attribute $attribute */
        $attribute = $this->checkoutAttributeRepository->get($attributeCode);
        $methods = (array)$attribute->getShippingMethods();

        return $methods && !in_array($quote->getShippingAddress()->getShippingMethod(), $methods, true);
    }
}
