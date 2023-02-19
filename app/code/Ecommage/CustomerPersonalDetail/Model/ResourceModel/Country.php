<?php

namespace Ecommage\CustomerPersonalDetail\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Country extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecommage_nation_code', 'entity_id');
    }
}
