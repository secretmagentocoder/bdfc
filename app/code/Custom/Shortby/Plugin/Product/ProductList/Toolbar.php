<?php
namespace Custom\Shortby\Plugin\Product\ProductList;

class Toolbar
{
    protected $_collection;

//     public function beforeSetCollection(
//         \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,
//         $collection
// ) {
//     // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/testing.log');
//     // $logger = new \Zend_Log();
//     // $logger->addWriter($writer);
//     // $logger->info(print_r($collection->getData()));

//         $collection->setOrder('price', 'asc');
//         return [$collection];
// }

    public function aroundGetAvailableOrders(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        \Closure $proceed
    ) {
        $result = $proceed();

        //make sure that each array key does exist, and then remove them
        if (array_key_exists('position', $result)) unset($result['position']);
        if (array_key_exists('name', $result)) unset($result['name']);
        // if (array_key_exists('price', $result)) unset($result['price']);

        return $result;
    }

}