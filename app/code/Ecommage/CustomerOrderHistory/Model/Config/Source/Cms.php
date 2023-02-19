<?php

namespace Ecommage\CustomerOrderHistory\Model\Config\Source;

class Cms implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct
    (
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $collectionFactory
    )
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $res = [];
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('is_active' , \Magento\Cms\Model\Page::STATUS_ENABLED);
        foreach($collection as $page){
            $data['value'] = $page->getData('identifier');
            $data['label'] = $page->getData('title');
            $res[] = $data;
        }
        return $res;
    }
}