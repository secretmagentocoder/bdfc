<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SpecialPromotions
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SpecialPromotions\Model\Condition;

class Combine extends \Magento\SalesRule\Model\Rule\Condition\Combine
{
   /**
    * Core event manager proxy
    *
    * @var \Magento\Framework\Event\ManagerInterface
    */
    protected $_eventManager = null;

    /**
     * @var \Webkul\SpecialPromotions\Model\Condition\Customer
     */
    protected $_conditionCustomer;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Address
     */
    protected $_conditionAddress;

   /**
    * Initialize
    *
    * @param \Magento\Rule\Model\Condition\Context $context
    * @param \Magento\Framework\Event\ManagerInterface $eventManager
    * @param \Webkul\SpecialPromotions\Model\Condition\Customer $conditionCustomer
    * @param \Magento\SalesRule\Model\Rule\Condition\Address $conditionAddress
    * @param array $data
    */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Webkul\SpecialPromotions\Model\Condition\Customer $conditionCustomer,
        \Magento\SalesRule\Model\Rule\Condition\Address $conditionAddress,
        array $data = []
    ) {
        $this->_conditionCustomer = $conditionCustomer;
        parent::__construct($context, $eventManager, $conditionAddress, $data);
        $this->setType(\Magento\SalesRule\Model\Rule\Condition\Combine::class);
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $customerAttributes = $this->_conditionCustomer->loadAttributeOptions()->getAttributeOption();
        $attributesCustomer = [];
        foreach ($customerAttributes as $code => $label) {
            $attributesCustomer[] = [
                'value' => 'Webkul\SpecialPromotions\Model\Condition\Customer|' . $code,
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                ['label' => __('Customer Attribute'), 'value' => $attributesCustomer]
            ]
        );
        return $conditions;
    }
}
