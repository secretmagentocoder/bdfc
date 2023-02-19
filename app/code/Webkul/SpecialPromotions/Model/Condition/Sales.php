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

class Sales extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerModel;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Customer $customerModel,
        array $data = []
    ) {
        $this->customerSession = $session;
        $this->customerModel = $customerModel;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            "num_of_order"       => __("Nunber of completed Orders") ,
            "total_sales_amount" => __("Total Sales Amount")
        ];
        $this->setAttributeOption($attributes);
        return $this;
    }

    /**
     * Get attribute element
     *
     * @return $this
     */
    public function getAttributeElement()
    {
        $attributElement = parent::getAttributeElement();
        $attributElement->setShowAsText(true);
        return $attributElement;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'base_subtotal':
            case 'weight':
            case 'total_qty':
                return 'numeric';

            case 'shipping_method':
            case 'payment_method':
            case 'country_id':
            case 'region_id':
                return 'select';
        }
        return 'string';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'shipping_method':
            case 'payment_method':
            case 'country_id':
            case 'region_id':
                return 'select';
        }
        return 'text';
    }

    /**
     * Get value select options
     *
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'region_id':
                    $options = $this->_directoryAllregion->toOptionArray();
                    break;

                case 'shipping_method':
                    $options = $this->_shippingAllmethods->toOptionArray();
                    break;

                case 'country_id':
                    $options = $this->_directoryCountry->toOptionArray();
                    break;

                case 'payment_method':
                    $options = $this->_paymentAllmethods->toOptionArray();
                    break;

                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $customer = $model;
        if (!$customer instanceof \Magento\Customer\Model\Customer) {
            if ($this->customerSession->isLoggedIn()) {
                $orders = $this->getOrders($this->customerSession->getId());
                $totalSale = $this->getTotalAmount($orders);
                $numberOrders = $orders->addFieldToFilter(
                    'status',
                    'complete'
                )->getSize();
                $customer->setNumOfOrder($numberOrders);
                $customer->setTotalSalesAmount($totalSale);
            }
        }
        return parent::validate($customer);
    }

    /**
     * Order Collection
     *
     * @param integer $customerId
     * @return collection object
     */
    private function getOrders($customerId)
    {
        $orders = $this->orderCollectionFactory->create()->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'customer_id',
            $customerId
        )->setOrder(
            'created_at',
            'desc'
        );
        return $orders;
    }
    
    /**
     * Get Total Amount
     *
     * @param object $orders
     * @return integer
     */
    private function getTotalAmount($orders)
    {
        $total = 0.0;
        foreach ($orders as $order) {
            $total = $total + $order->getGrandTotal();
        }
        return $total;
    }
}
