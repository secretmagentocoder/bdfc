<?php
namespace Custom\Api\Model\ResourceModel\Offerline;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Custom\Api\Model\Offerline as Model;
use Custom\Api\Model\ResourceModel\Offerline as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}