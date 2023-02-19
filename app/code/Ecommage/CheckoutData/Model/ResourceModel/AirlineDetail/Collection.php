<?php
namespace Ecommage\CheckoutData\Model\ResourceModel\AirlineDetail;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'ecommage_checkout_airline_detail_collection';
    protected $_eventObject = 'airline_detail_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ecommage\CheckoutData\Model\AirlineDetail', 'Ecommage\CheckoutData\Model\ResourceModel\AirlineDetail');
    }

}
