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
namespace Webkuls\SpecialPromotions\Observer;

use Magento\Framework\Event\ObserverInterface;

class ConditionCombineObserver implements ObserverInterface
{
    /**
     * @param \Webkuls\SpecialPromotions\Model\Condition\Customer $conditionCustomer
     * @param \Webkuls\SpecialPromotions\Model\Condition\Sales $conditionSales
     */
    public function __construct(
        \Webkuls\SpecialPromotions\Model\Condition\Customer $conditionCustomer,
        \Webkuls\SpecialPromotions\Model\Condition\Sales $conditionSales
    ) {
        $this->_conditionCustomer = $conditionCustomer;
        $this->_conditionSales = $conditionSales;
    }

    /**
     * add option in rule conditions
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerAttributes = $this->_conditionCustomer->loadAttributeOptions()->getAttributeOption();
        $salesAttributes = $this->_conditionSales->loadAttributeOptions()->getAttributeOption();
        $attributesCustomer = [];
        foreach ($customerAttributes as $code => $label) {
            $attributesCustomer[] = [
            'value' => \Webkuls\SpecialPromotions\Model\Condition\Customer::class.'|' . $code,
            'label' => $label,
            ];
        }
        $attributesSales = [];
        foreach ($salesAttributes as $code => $label) {
            $attributesSales[] = [
            'value' => \Webkuls\SpecialPromotions\Model\Condition\Sales::class.'|' . $code,
            'label' => $label,
            ];
        }
        $additional = $observer->getAdditional();
        $subSelectionClass =  \Webkuls\SpecialPromotions\Model\Condition\Order\Subselect::class;
        $additional->setConditions(
            [
            ['label' => __('Customer Attribute'), 'value' => $attributesCustomer],
            ['label' => __('Purchase History'), 'value' => $attributesSales],
            ['label' => __('Orders Subselection'), 'value' => $subSelectionClass]
            ]
        );
    }
}
