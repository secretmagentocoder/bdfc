<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Api\Data;

interface RaffleTicketsInterface
{
    public const ID = 'id';
    public const SHOW_WINNER = 'show_winner';
    public const RAFFLE_PRODUCT_ID = 'raffle_product_id';
    public const RAFFLE_PRODUCT_NAME = 'raffle_product_name';
    public const RAFFLE_PRODUCT_SERIES     = 'raffle_product_series';
    public const WINNER_TICKET_NUMBER = 'winner_ticket_number';
    public const WINNER_NAME = 'winner_name';
    public const NATIONALITY = 'nationality';
    public const PRIZE = 'prize';
    public const DRAW_DATE = 'draw_date';
    public const MEDIA  = 'media';
    
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get Show winner on frontend
     *
     * @return $this
     */
    public function getShowWinner();

    /**
     * Set Show winner on frontend
     *
     * @param  int $show_winner
     * @return $this
     */
    public function setShowWinner($show_winner);

    /**
     * Get Raffle Product ID
     *
     * @return int
     */
    public function getRaffleProductId();

    /**
     * Set Raffle Product ID
     *
     * @param int $raffle_product_Id
     * @return $this
     */
    public function setRaffleProductId($raffle_product_Id);

    /**
     * Get Raffle Product Name
     *
     * @return string
     */
    public function getRaffleProductName();

    /**
     * Set Raffle Product Name
     *
     * @param string $raffle_product_name
     * @return $this
     */
    public function setRaffleProductName($raffle_product_name);

    /**
     * Get Raffle Product Series
     *
     * @return string
     */
    public function getRaffleProductSeries();

    /**
     * Set Raffle Product Series
     *
     * @param string $raffle_product_series
     * @return $this
     */
    public function setRaffleProductSeries($raffle_product_series);

    /**
     * Get Winner Ticket Number
     *
     * @return string
     */
    public function getWinnerTicketNumber();

    /**
     * Set Winner Ticket Number
     *
     * @param string $winner_ticket_number
     * @return $this
     */
    public function setWinnerTicketNumber($winner_ticket_number);

    /**
     * Get Winner Name
     *
     * @return string
     */
    public function getWinnerName();

    /**
     * Set Winner Name
     *
     * @param string $winner_name
     * @return $this
     */
    public function setWinnerName($winner_name);

    /**
     * Get Nationality
     *
     * @return string
     */
    public function getNationality();

    /**
     * Set Nationality
     *
     * @param string $nationality
     * @return $this
     */
    public function setNationality($nationality);

    /**
     * Get Prize
     *
     * @return string
     */
    public function getPrize();

    /**
     * Set Prize
     *
     * @param string $prize
     * @return $this
     */
    public function setPrize($prize);

    /**
     * Get Draw Date
     *
     * @return date
     */
    public function getDrawDate();

    /**
     * Set Draw Date
     *
     * @param date $draw_date
     * @return $this
     */
    public function setDrawDate($draw_date);

    /**
     * Get Media
     *
     * @return string
     */
    public function getMedia();

    /**
     * Set Media
     *
     * @param string $media
     * @return $this
     */
    public function setMedia($media);
}
