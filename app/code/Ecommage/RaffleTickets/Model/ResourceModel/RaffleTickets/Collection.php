<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Model\ResourceModel\RaffleTickets;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Collection initialisation
     */
    protected function _construct()
    {
        $this->_init(
            \Ecommage\RaffleTickets\Model\RaffleTickets::class,
            \Ecommage\RaffleTickets\Model\ResourceModel\RaffleTickets::class
        );
        $this->_idFieldName = 'id';
    }
}
