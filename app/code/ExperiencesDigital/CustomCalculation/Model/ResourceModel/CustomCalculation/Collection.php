<?php

namespace ExperiencesDigital\CustomCalculation\Model\ResourceModel\CustomCalculation;
use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection  extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected function _construct()
    {
        $this->_init(\ExperiencesDigital\CustomCalculation\Model\CustomCalculation::class, \ExperiencesDigital\CustomCalculation\Model\ResourceModel\CustomCalculation::class);
    }
}