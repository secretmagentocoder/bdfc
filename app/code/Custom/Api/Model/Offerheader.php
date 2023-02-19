<?php

namespace Custom\Api\Model;

use Magento\Framework\Model\AbstractModel;
use Custom\Api\Model\ResourceModel\Offerheader as ResourceModel;

class Offerheader extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}