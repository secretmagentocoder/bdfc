<?php
declare(strict_types=1);

namespace Amasty\Orderattr\Model\Attribute\InputType\FrontendCaster;

use Amasty\Orderattr\Api\Data\CheckoutAttributeInterface;

class Multiselect implements SpecificationProcessorInterface
{
    /**
     * @param string[] $element
     * @param CheckoutAttributeInterface $attribute
     */
    public function processSpecificationByAttribute(array &$element, CheckoutAttributeInterface $attribute): void
    {
        $element['size'] = (int)$attribute->getMultiselectSize();
    }
}
