<?php

namespace Ecommage\OrderRaffle\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Framework\Pricing\Helper\Data as priceHelper;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order\Config;

class Raffle extends Template
{
  protected $orderCollectionFactory;
  protected $priceHepler;

    public function __construct(
        Context $context,
        CollectionFactory $orderCollectionFactory,
        priceHelper $priceHepler,
        ItemFactory $itemFactory,
        Config $config,
        \Magento\Eav\Model\Config $eavConfig,
        Session $customerSession
    ) {
        $this->itemFactory = $itemFactory;
        $this->customerSession = $customerSession;
        $this->orderConfig = $config;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->priceHepler = $priceHepler;
        $this->eavConfig = $eavConfig;
        parent::__construct($context);
    }

    protected function _prepareLayout()
    {
    parent::_prepareLayout();
        if ($this->getCustomCollection()) {
            $pager = $this->getLayout()->createBlock(
            'Magento\Theme\Block\Html\Pager',
            'custom.history.pager'
            )->setAvailableLimit([5 => 5, 10 => 10, 15 => 15, 20 => 20])
            ->setShowPerPage(true)->setCollection(
                $this->getCustomCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCustomCollection()->load();
        }
        return $this;
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    public function getCustomCollection()
    {
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest(
            
        )->getParam('limit') : 5;
        $attributeCode = 'is_check_raffle';
        $alias = $attributeCode.'_table';
        $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);

        $collection = $this->itemFactory->create()->getCollection();
        $collection->getSelect()->join(
            ['product' => 'catalog_product_entity'],
            "main_table.product_id = product.entity_id",
            ['row_id']
        );
        $collection->getSelect()->join(
            [$alias => $attribute->getBackendTable()],
            "product.row_id = $alias.row_id AND $alias.attribute_id={$attribute->getId()}",
            [$attributeCode => 'value']
        );
        $collection->getSelect()->joinLeft(
            ['sales_flat_order' => 'sales_order'],
            'main_table.order_id = sales_flat_order.entity_id ',
            ['sales_flat_order.status','sales_flat_order.increment_id','sales_flat_order.created_at']
        )->where("sales_flat_order.customer_id=". $this->customerSession->getId()." and $alias.value=1");
        
        $collection->setPageSize($pageSize)->setOrder(
            'sales_flat_order.created_at',
            'desc'
        );;
        $collection->setCurPage($page);
        
        return $collection;
    }
    public function getFormattedPrice($price)
    {
        return $this->priceHepler->currency(number_format($price, 2), true, false);
    }

    public function getTicketData($options)
    {
        $ticketData = '';
        if (!empty($options)) {
            if (isset($options['options'])) {
                $super_attribute = $options['options'];
                $ticketData = '<dl class="item-options">';
                foreach ($super_attribute as $key => $value) {
                    $ticketData .= '<span class="option">';
                    $label = $value['label'];
                    if($value['label'] == 'Raffle Ticket') {
                        $label = 'Ticket No.';
                    }
                    $ticketData .= $label.': '.$value['print_value'];
                    $ticketData .= '</br>';
                    $ticketData .= '</span>';
                }
                $ticketData .= '</dl>';
            }
        }
        return $ticketData;
    }
}