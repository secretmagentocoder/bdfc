<?php

namespace Custom\Api\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Offerline extends AbstractDb
{
    protected $_isPkAutoIncrement = false;
    
    protected function _construct()
    {
        $this->_init('web_offer_line', 'id');
    }
}