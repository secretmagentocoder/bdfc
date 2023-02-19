<?php
namespace Ecommage\CheckoutData\Model\ResourceModel;

class Airline extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('ecommage_airline_list', 'airline_id');
    }

}
