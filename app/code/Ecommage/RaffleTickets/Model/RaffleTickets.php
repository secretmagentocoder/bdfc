<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Model;

use Magento\Framework\Model\AbstractModel;

class RaffleTickets extends AbstractModel
{
    /**
     * RaffleTickets constructor
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Ecommage\RaffleTickets\Model\ResourceModel\RaffleTickets::class);
    }
}
