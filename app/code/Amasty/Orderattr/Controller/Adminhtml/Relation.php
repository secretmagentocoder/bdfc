<?php

namespace Amasty\Orderattr\Controller\Adminhtml;

abstract class Relation extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Orderattr::attributes_relation';
}
