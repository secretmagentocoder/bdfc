<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Model\Data;

use Ecommage\RaffleTickets\Model\RaffleTickets as RaffleTicketsData;
use Ecommage\RaffleTickets\Api\Data\RaffleTicketsInterface;

class RaffleTickets extends RaffleTicketsData implements RaffleTicketsInterface
{
    /**
     * Get id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(RaffleTicketsInterface::ID);
    }

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(RaffleTicketsInterface::ID, $id);
    }

    /**
     * Get Show winner on frontend
     *
     * @return boolean
     */
    public function getShowWinner()
    {
        return $this->getData(RaffleTicketsInterface::SHOW_WINNER);
    }

    /**
     * Set Show winner on frontend
     *
     * @param boolean $show_winner
     * @return $this
     */
    public function setShowWinner($show_winner)
    {
        return $this->setData(RaffleTicketsInterface::SHOW_WINNER, $show_winner);
    }

    /**
     * Get Raffle Product ID
     *
     * @return int
     */
    public function getRaffleProductId()
    {
        return $this->getData(RaffleTicketsInterface::RAFFLE_PRODUCT_ID);
    }

    /**
     * Set Raffle Product Id
     *
     * @param int $raffle_product_Id
     * @return $this
     */
    public function setRaffleProductId($raffle_product_Id)
    {
        return $this->setData(RaffleTicketsInterface::RAFFLE_PRODUCT_ID, $raffle_product_Id);
    }

    /**
     * Get Raffle Product Name
     *
     * @return string
     */
    public function getRaffleProductName()
    {
        return $this->getData(RaffleTicketsInterface::RAFFLE_PRODUCT_NAME);
    }

    /**
     * Set Raffle Product Series
     *
     * @param int $raffle_product_name
     * @return $this
     */
    public function setRaffleProductName($raffle_product_name)
    {
        return $this->setData(RaffleTicketsInterface::RAFFLE_PRODUCT_NAME, $raffle_product_name);
    }
    
    /**
     * Get Raffle Product Series
     *
     * @return string
     */
    public function getRaffleProductSeries()
    {
        return $this->getData(RaffleTicketsInterface::RAFFLE_PRODUCT_SERIES);
    }

    /**
     * Set Raffle Product Series
     *
     * @param int $raffle_product_series
     * @return $this
     */
    public function setRaffleProductSeries($raffle_product_series)
    {
        return $this->setData(RaffleTicketsInterface::RAFFLE_PRODUCT_SERIES, $raffle_product_series);
    }

    /**
     * Get Winner Ticket Number
     *
     * @return string
     */
    public function getWinnerTicketNumber()
    {
        return $this->getData(RaffleTicketsInterface::WINNER_TICKET_NUMBER);
    }

    /**
     * Get Winner Ticket Number
     *
     * @param string $winner_ticket_number
     * @return $this
     */
    public function setWinnerTicketNumber($winner_ticket_number)
    {
        return $this->setData(RaffleTicketsInterface::WINNER_TICKET_NUMBER, $winner_ticket_number);
    }

    /**
     * Get Winner Name
     *
     * @return string
     */
    public function getWinnerName()
    {
        return $this->getData(RaffleTicketsInterface::WINNER_NAME);
    }

    /**
     * Set Winner Name
     *
     * @param string $winner_name
     * @return $this
     */
    public function setWinnerName($winner_name)
    {
        return $this->setData(RaffleTicketsInterface::WINNER_NAME, $winner_name);
    }

    /**
     * Get Nationality
     *
     * @return string
     */
    public function getNationality()
    {
        return $this->getData(RaffleTicketsInterface::NATIONALITY);
    }

    /**
     * Set Nationality
     *
     * @param string $nationality
     * @return $this
     */
    public function setNationality($nationality)
    {
        return $this->setData(RaffleTicketsInterface::NATIONALITY, $nationality);
    }

    /**
     * Get Prize
     *
     * @return string
     */
    public function getPrize()
    {
        return $this->getData(RaffleTicketsInterface::PRIZE);
    }

    /**
     * Set Prize
     *
     * @param string $prize
     * @return $this
     */
    public function setPrize($prize)
    {
        return $this->setData(RaffleTicketsInterface::PRIZE, $prize);
    }

    /**
     * Get Draw Date
     *
     * @return date
     */
    public function getDrawDate()
    {
        return $this->getData(RaffleTicketsInterface::DRAW_DATE);
    }

    /**
     * Set Draw Date
     *
     * @param date $draw_date
     * @return $this
     */
    public function setDrawDate($draw_date)
    {
        return $this->setData(RaffleTicketsInterface::DRAW_DATE, $draw_date);
    }

    /**
     * Get Media
     *
     * @return string
     */
    public function getMedia()
    {
        return $this->getData(RaffleTicketsInterface::MEDIA);
    }

    /**
     * Get Media
     *
     * @param string $media
     * @return $this
     */
    public function setMedia($media)
    {
        return $this->setData(RaffleTicketsInterface::MEDIA, $media);
    }
}
