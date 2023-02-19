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
namespace Webkul\SpecialPromotions\Observer;

use Magento\Framework\Event\ObserverInterface;

class ConditionCombineObserver implements ObserverInterface
{
    /**
     * @param \Webkul\SpecialPromotions\Model\Condition\Customer $conditionCustomer
     * @param \Webkul\SpecialPromotions\Model\Condition\Sales $conditionSales
     */
    public function __construct(
        \Webkul\SpecialPromotions\Model\Condition\Customer $conditionCustomer,
        \Webkul\SpecialPromotions\Model\Condition\Sales $conditionSales
    ) {
        $this->_conditionCustomer = $conditionCustomer;
        $this->_conditionSales = $conditionSales;
    }

    /**
     * Add option in rule conditions
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
            'value' => \Webkul\SpecialPromotions\Model\Condition\Customer::class.'|' . $code,
            'label' => $label,
            ];
        }
        $attributesSales = [];
        foreach ($salesAttributes as $code => $label) {
            $attributesSales[] = [
            'value' => \Webkul\SpecialPromotions\Model\Condition\Sales::class.'|' . $code,
            'label' => $label,
            ];
        }
        $additional = $observer->getAdditional();
        $subSelectionClass =  \Webkul\SpecialPromotions\Model\Condition\Order\Subselect::class;
        $additional->setConditions(
            [
            ['label' => __('Customer Attribute'), 'value' => $attributesCustomer],
            ['label' => __('Purchase History'), 'value' => $attributesSales],
            ['label' => __('Orders Subselection'), 'value' => $subSelectionClass]
            ]
        );
    }
}
