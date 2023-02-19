<?php

namespace ExperiencesDigital\CustomCalculation\Model\ResourceModel;

class CustomCalculation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('custom_category_calculation', 'id');
    }
}