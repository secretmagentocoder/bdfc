<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RaffleTickets extends AbstractDb
{
    /**
     * Define the table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('raffle_ticket_winners', 'id');
    }
}
