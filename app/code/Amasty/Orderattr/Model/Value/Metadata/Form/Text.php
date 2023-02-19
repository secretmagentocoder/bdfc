<?php

namespace Amasty\Orderattr\Model\Value\Metadata\Form;

class Text extends \Magento\Eav\Model\Attribute\Data\Text
{
    /**
     * Export attribute value to entity model
     *
     * @param array|string $value
     * @return $this
     */
    public function compactValue($value)
    {
        if ($value !== false) {
            /*
             * avoid snake_case to CamelCale and vice versa convertation
             * because underscore in attribute_code can be lost
             */
            $this->getEntity()->setData($this->getAttribute()->getAttributeCode(), $value);
        }
        return $this;
    }
}
