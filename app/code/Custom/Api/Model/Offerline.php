<?php

namespace Custom\Api\Model;

use Magento\Framework\Model\AbstractModel;
use Custom\Api\Model\ResourceModel\Offerline as ResourceModel;

class Offerline extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}