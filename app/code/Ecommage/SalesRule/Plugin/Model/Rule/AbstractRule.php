<?php

namespace Ecommage\SalesRule\Plugin\Model\Rule;

use Magento\Framework\Registry;

abstract class AbstractRule
{
    const SKU_IS_ONE_OF = 'sku_is_one_of';
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * ConditionProduct constructor.
     *
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }
}
