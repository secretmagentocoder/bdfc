<?php

namespace Ecommage\CustomerPersonalDetail\Model;


class Country extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ecommage\CustomerPersonalDetail\Model\ResourceModel\Country');
    }
}
