<?php
namespace Custom\Api\Model\ResourceModel\Offerheader;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Custom\Api\Model\Offerheader as Model;
use Custom\Api\Model\ResourceModel\Offerheader as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}