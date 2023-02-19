<?php
namespace Ecommage\CheckoutData\Model\ResourceModel\Airline;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'airline_id';
    protected $_eventPrefix = 'ecommage_checkout_airline_collection';
    protected $_eventObject = 'airline_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ecommage\CheckoutData\Model\Airline', 'Ecommage\CheckoutData\Model\ResourceModel\Airline');
    }

}
