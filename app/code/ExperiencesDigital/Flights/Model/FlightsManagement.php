<?php

declare(strict_types=1);

namespace ExperiencesDigital\Flights\Model;

class FlightsManagement implements \ExperiencesDigital\Flights\Api\FlightsManagementInterface
{
    protected $objectManager;
    protected $resourceConnection;



    public function __construct()
    {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->resourceConnection = $this->objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
    }


    /**
     * {@inheritdoc}
     */
    public function getFlights($flight_no, $flight_date)
    {
        $query = $this->resourceConnection->fetchAll("SELECT * FROM flights left join airlines on flights.Airline=airlines.Code where FNO='$flight_no' and Flight_Date='$flight_date'");
        
         return isset($query[0]) ? $query : '';
    }
}
