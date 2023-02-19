<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Block\Raffle;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Ecommage\RaffleTickets\Api\Data\RaffleTicketsInterfaceFactory;
use Magento\Directory\Model\CountryFactory;

class Winners extends Template
{
    /**
     * @var RaffleTicketsInterfaceFactory
     */
    private $raffleTicketsInterfaceFactory;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * Constructor parameters
     *
     * @param Context                               $context
     * @param RaffleTicketsInterfaceFactory         $raffleTicketsInterfaceFactory
     * @param CountryFactory                        $countryFactory
     * @param array                                 $data
     */
    public function __construct(
        Context $context,
        RaffleTicketsInterfaceFactory $raffleTicketsInterfaceFactory,
        CountryFactory $countryFactory,
        array $data = []
    ) {
        
        $this->raffleTicketsInterfaceFactory = $raffleTicketsInterfaceFactory;
        $this->countryFactory = $countryFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get winner list
     */
    public function getRaffleWinners()
    {
        $winnersList = $this->raffleTicketsInterfaceFactory->create()->getCollection()
            ->addFieldToFilter('show_winner', 1)
            ->setOrder('draw_date', 'DESC');
        return $winnersList;
    }
    
    /**
     * Get country name
     *
     * @param string $countryId
     * return string
     */
    public function getCountryName($countryId)
    {
        $countryFactory = $this->countryFactory->create();
        $country = $countryFactory->loadByCode($countryId);
        $countryName = $country->getName();
        return $countryName;
    }
}
