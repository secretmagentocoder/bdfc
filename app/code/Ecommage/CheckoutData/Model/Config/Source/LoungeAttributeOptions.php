<?php
namespace Ecommage\CheckoutData\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class LoungeAttributeOptions extends AbstractSource
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options=[
                ['label' => __('Duty free shop'), 'value' => 'Duty free shop'],
                ['label' => __('GAT'), 'value' => 'GAT']
            ];
        }
        return $this->_options;
    }
}
