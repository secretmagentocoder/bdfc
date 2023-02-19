<?php
namespace Amasty\Orderattr\Model\Attribute\InputType\FrontendCaster;

use Amasty\Orderattr\Api\Data\CheckoutAttributeInterface;

/**
 * Service Provider Interface - SPI
 */
interface SpecificationProcessorInterface
{
    /**
     * @param string[] $element
     * @param CheckoutAttributeInterface $attribute
     * @return void
     */
    public function processSpecificationByAttribute(array &$element, CheckoutAttributeInterface $attribute): void;
}
