<?php

namespace Amasty\Orderattr\Model\Order;

use Magento\Backend\Model\Session;

class Storage
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(
        Session $session
    ) {
        $this->session = $session;
    }

    /**
     * @param $orderIds
     * @return $this
     */
    public function setOrderIds($orderIds)
    {
        $this->session->setOrderIds($orderIds);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderIds()
    {
        return $this->session->getOrderIds();
    }
}
