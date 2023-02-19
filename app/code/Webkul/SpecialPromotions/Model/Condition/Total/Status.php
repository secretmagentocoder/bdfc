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

use Magento\Catalog\Model\ProductCategoryList;

class Status extends \Magento\Rule\Model\Condition\Product\AbstractProduct
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

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
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
     * @param array $data
     * @param ProductCategoryList $categoryList
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory,
        array $data = [],
        ProductCategoryList $categoryList = null
    ) {
        $this->statusCollectionFactory = $statusCollectionFactory;
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data,
            $categoryList
        );
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
         $this->setAttributeOption([
            "order_status" =>"Order Status"
         ]);

        return $this;
    }
    
    /**
     * Get status options
     *
     * @return array
     */
    public function getStatusOptions()
    {
        $options = $this->statusCollectionFactory->create()->toOptionArray();
        return $options;
    }

    /**
     * Element Type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return "select";
    }

     /**
      * Retrieve input type
      *
      * @return string
      */
    public function getInputType()
    {
        return "select";
    }

    /**
     * Prepare options value
     *
     * @return current reference
     */
    protected function _prepareValueOptions()
    {
        $orderStatuses =  $this->getStatusOptions();
        foreach ($orderStatuses as $status) {
            $selectOptions[] = [
                    'value' => $status["value"],
                    'label' => $status["label"]
                ];
        }
        $this->_setSelectOptions($selectOptions, null, []);
        return $this;
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
            if ($attr == 'order_status') {
                $orders->addFieldToFilter(
                    'status',
                    $value
                );
                $ordersTotalAmount = $this->getAllOrderAmount($orders);
                $total = ($ordersTotalAmount / (($orders->getSize()) ? $orders->getSize() : 1));
            }
        }
        return $this->validateAttribute($total);
    }

    /**
     * Orders Tatal
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
    
    /**
     * Retrieve value element chooser URL
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $url = 'sales_rule/promo_widget/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;
            default:
                break;
        }
        return $url !== false ? $this->_backendData->getUrl($url) : '';
    }
}
