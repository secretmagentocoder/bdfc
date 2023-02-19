<?php

namespace Ecommage\OrderRaffle\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    const ORDER_RAFFLE = 'ecommage_order_raffle_order_raffle';
    const CHANGE_ACCOUNT = 'ecommage_update_account_update_change';
    const UPDATE_ACCOUNT = 'ecommage_customer_update_account_index';
    const WISH_LIST = 'wishlist_index_index';
    const ORDER_HISTORY = 'sales_order_history';

    public function __construct
    (
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $config,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory ,
        Context $context
    )
    {
        $this->customerSession = $customerSession;
        $this->_orderConfig = $config;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }

    public function getOrderCollectionByCustomerId()
    {
        if (!$this->customerSession->getId())
        {
            return  [];
        }
        $collection = $this->_orderCollectionFactory->create($this->customerSession->getId())
                           ->addFieldToSelect('*')
                           ->addFieldToFilter('status',
                                              ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
                           )
                           ->setOrder(
                               'created_at',
                               'desc'
                           );

        return $collection;

    }

    public function getBreadcrumb($actionName)
    {
        $breadcrumb = 'My Account';
        if ($actionName == self::ORDER_RAFFLE)
        {
            $breadcrumb = 'Raffle Tickets';
        }
        if ($actionName == self::UPDATE_ACCOUNT)
        {
            $breadcrumb = 'Personal Detail';
        }
        if ($actionName == self::CHANGE_ACCOUNT)
        {
            $breadcrumb = 'Change Password';
        }
        if ($actionName == self::WISH_LIST)
        {
            $breadcrumb = 'WishList';
        }
        return $breadcrumb;
    }
}