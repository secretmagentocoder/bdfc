<?php
/**
 * Webkuls Software.
 *
 * @category  Webkuls
 * @package   Webkuls_SpecialPromotions
 * @author    Webkuls
 * @copyright Copyright (c) Webkuls Software Private Limited (https://Webkuls.com)
 * @license   https://store.Webkuls.com/license.html
 */
namespace Webkul\SpecialPromotions\Model\Condition\Order;

use Magento\Sales\Model\ResourceModel\Order\Collection;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setType(\Webkul\SpecialPromotions\Model\Condition\Order\Combine::class);
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
         return [
                [
                    'label' => __('Please Choose Condition'),
                    'value' => ''
                ],
                [
                    'label' => __('Order Status'),
                    'value' => \Webkul\SpecialPromotions\Model\Condition\Total\Status::class
                ],
                [
                    'label' => __('Duration After order is Placed'),
                    'value' => \Webkul\SpecialPromotions\Model\Condition\Total\Duration::class
                ]
            ];
    }

    /**
     * Collect validated attributes
     *
     * @param Collection $orderCollection
     * @return $this
     */
    public function collectValidatedAttributes($orderCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($orderCollection);
        }
        return $this;
    }
}
