<?php
/**
 * Webkuls Software.
 *
 * @category  Webkuls
 * @package   Webkul_SpecialPromotions
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SpecialPromotions\Model\Condition\Order;

class Subselect extends \Webkul\SpecialPromotions\Model\Condition\Order\Combine
{
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
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct,
        \Magento\Customer\Model\Session $session,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
        $this->customerSession = $session;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $data);
        $this->setType(\Webkul\SpecialPromotions\Model\Condition\Order\Subselect::class)->setValue(null);
    }

    /**
     * Load array
     *
     * @param array $conditionArray
     * @param string $key
     * @return $this
     */
    public function loadArray($conditionArray, $key = 'conditions')
    {
        $this->setAttribute($conditionArray['attribute']);
        $this->setOperator($conditionArray['operator']);
        parent::loadArray($conditionArray, $key);
        return $this;
    }

    /**
     * Return as xml
     *
     * @param string $containerKey
     * @param string $itemKey
     * @return string
     */
    public function asXml($containerKey = 'conditions', $itemKey = 'condition')
    {
        $xml = '<attribute>' .
            $this->getAttribute() .
            '</attribute>' .
            '<operator>' .
            $this->getOperator() .
            '</operator>' .
            parent::asXml(
                $containerKey,
                $itemKey
            );
        return $xml;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(
            ['average_order_value' => __('Average Order Value'),
             'orders_total_amount' => __('Total Sales Amount'),
             'no_of_placed_orders' => __('Number of Placed Orders')
            ]
        );
        return $this;
    }

    /**
     * Load value options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        return $this;
    }

    /**
     * Load operator options
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            [
                '==' => __('is'),
                '!=' => __('is not'),
                '>=' => __('equals or greater than'),
                '<=' => __('equals or less than'),
                '>' => __('greater than'),
                '<' => __('less than'),
                '()' => __('is one of'),
                '!()' => __('is not one of'),
            ]
        );
        return $this;
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Return as html
     *
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() . __(
            "If %1 %2 %3 for a subselection of items in cart matching %4 of these conditions:",
            $this->getAttributeElement()->getHtml(),
            $this->getOperatorElement()->getHtml(),
            $this->getValueElement()->getHtml(),
            $this->getAggregatorElement()->getHtml()
        );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    /**
     * Get Status and Duration for condition
     *
     * @param object $conditions
     * @return integer
     */
    private function getStatusNDuration($conditions)
    {
        $status = $duration = '';
        $statusOp = $durationOp = '==';
        foreach ($this->getConditions() as $subSelection) {
            if ($subSelection->getAttribute() == 'order_status') {
                $status = $subSelection->getValue();
                $statusOp = $subSelection->getOperator();
            }
            if ($subSelection->getAttribute() == 'duration') {
                $duration = $subSelection->getValue();
                $durationOp = $subSelection->getOperator();
            }
        }
        return [$status, $statusOp, $duration, $durationOp];
    }

    /**
     * This function is used to return orders as per conditions
     *
     * @param Object $orders
     * @param string $statusOp
     * @param string $status
     * @return object
     */
    private function getCustomerOrders($orders, $statusOp, $status)
    {
        if ($statusOp == '==') {
            $orders = $orders->addFieldToFilter('status', ['eq' => $status]);
        } else {
            $orders = $orders->addFieldToFilter('status', ['neq' => $status]);
        }
        return $orders;
    }

    /**
     * Validate
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        if (!$this->getConditions()) {
            return false;
        }
        $aggre = $this->getAggregator();
        $status = $duration = '';
        $statusOp = $durationOp = '==';
        $cond    = $this->getConditions();
        list(
            $status,
            $statusOp,
            $duration,
            $durationOp
        ) = $this->getStatusNDuration($cond);
        $attr = $this->getAttribute();
        $total = 0;
        $quote = $model->getQuote();
        $check = 0;
        if ($this->customerSession->isLoggedIn()) {
            $orders = $this->getCustomerOrders($this->customerSession->getId());
            
            if ($status != '') {
                $orders = $this->getCustomerOrders($orders, $statusOp, $status);
            }
            if ($duration != '') {
                $check = 1;
                foreach ($orders as $order) {
                    $createdAt = date_create($order->getCreatedAt());
                    $currentDate = date_create(date("Y-m-d"));
                    $differenceBetween  = date_diff($createdAt, $currentDate);
                    if ($this->checkDuration($differenceBetween, $durationOp, $duration)) {
                        $check = 0;
                    }
                    break;
                }
            }
            $ordersTotalAmount = $this->getAllOrderAmount($orders);
            if ($attr == 'average_order_value') {
                $total = ($ordersTotalAmount / (($orders->getSize()) ? $orders->getSize() : 1));
            }
            if ($attr == 'orders_total_amount') {
                $total = $ordersTotalAmount;
            }
            if ($attr == 'no_of_placed_orders') {
                $total = $orders->getSize();
            }
            $total = ($check) ? 0 : $total;
        }
        return $this->validateAttribute($total);
    }

    /**
     * Check duration
     *
     * @param string $diff
     * @param string $durationOp
     * @param integer $duration
     * @return string
     */
    private function checkDuration($diff, $durationOp, $duration)
    {
        $return = false;
        switch ($durationOp) {
            case '>=':
                $return = ($diff >= $duration) ? true : false;
                break;
            case '<=':
                $return = ($diff <= $duration) ? true : false;
                break;
            case '>':
                $return = ($diff > $duration) ? true : false;
                break;
            case '<':
                $return = ($diff < $duration) ? true : false;
                break;
            case '==':
                $return = ($diff == $duration) ? true : false;
                break;
        }
         return $return;
    }

    /**
     * get order total
     *
     * @param object $orders
     * @return integer
     */
    private function getAllOrderAmount($orders)
    {
        $total = 0;
        foreach ($orders as $order) {
            $total += $order->getGrandTotal();
        }
        return $total;
    }

    /**
     * Order collection
     *
     * @param integer $customerId
     * @param string $status
     * @return object
     */
    private function getOrders($customerId, $status = '')
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
