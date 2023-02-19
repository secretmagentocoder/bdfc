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
namespace Webkul\SpecialPromotions\Model\Condition\Total;

class Duration extends \Magento\Rule\Model\Condition\AbstractCondition
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
      * @var Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
      */
    protected $statusCollectionFactory;

    /**
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory,
        array $data = []
    ) {
        $this->customerSession = $session;
        $this->customerModel = $customerModel;
        $this->statusCollectionFactory = $statusCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Default operator options getter
     *
     * Provides all possible operator options
     *
     * @return array
     */
    public function getDefaultOperatorOptions()
    {
        if (null === $this->_defaultOperatorOptions) {
            $this->_defaultOperatorOptions = [
                '>=' => __('equals or greater than'),
                '<=' => __('equals or less than'),
                '>' => __('greater than'),
                '<' => __('less than'),
                '==' => __('is'),
            ];
        }
        return $this->_defaultOperatorOptions;
    }
    
    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            "duration"   => "Duration after order was placed",
            
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
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'weight':
            case 'total_qty':
                return 'numeric';

            case 'shipping_method':
            case 'payment_method':
            case 'country_id':
            case 'base_subtotal':
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
            case 'country_id':
            case 'payment_method':
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
                case 'country_id':
                    $options = $this->_directoryCountry->toOptionArray();
                    break;

                case 'region_id':
                    $options = $this->_directoryAllregion->toOptionArray();
                    break;

                case 'payment_method':
                    $options = $this->_paymentAllmethods->toOptionArray();
                    break;

                case 'shipping_method':
                    $options = $this->_shippingAllmethods->toOptionArray();
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
        $attr = $this->getAttribute();
        $total = 0;
        $quote = $model->getQuote();
        $value = $this->getValue();
        if ($this->customerSession->isLoggedIn()) {
            $orders = $this->getOrders($this->customerSession->getId());
            if ($attr == 'duration') {
                foreach ($orders as $orderData) {
                    $createdAt = date_create($orderData->getCreatedAt());
                    $currentDate = date_create(date("Y-m-d"));
                    $differenceBetween  = date_diff($createdAt, $currentDate);
                    $total = $differenceBetween;
                    break;
                }
            }
        }
        return $this->validateAttribute($total);
    }

    /**
     * This function returns all order as per customer id.
     *
     * @param integer $customerId
     * @return object
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
}
