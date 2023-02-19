<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Ecommage\RaffleTickets\Api\Data\RaffleTicketsInterface;

interface RaffleTicketsRepositoryInterface
{
    /**
     * Save RaffleTicket Data
     *
     * @param RaffleTicketsInterface $data
     */
    public function save(RaffleTicketsInterface $data);

    /**
     * Get By ID
     *
     * @param Int $id
     * @return mixed
     */
    public function getById($id);

    /**
     * Get List
     *
     * @param  SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete By ID
     *
     * @param  Int $id
     * @return mixed
     */
    public function deleteById($id);
}
