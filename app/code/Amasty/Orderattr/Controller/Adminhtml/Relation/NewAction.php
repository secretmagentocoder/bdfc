<?php

namespace Amasty\Orderattr\Controller\Adminhtml\Relation;

class NewAction extends \Amasty\Orderattr\Controller\Adminhtml\Relation
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
