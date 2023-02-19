<?php

namespace Amasty\Orderattr\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class TimeFormat implements ArrayInterface
{
    const HOUR_12 = 0;
    const HOUR_24 = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::HOUR_12 => __('12-hour AM/PM format'),
            self::HOUR_24 => __('24-hour format'),
        ];
    }
}
