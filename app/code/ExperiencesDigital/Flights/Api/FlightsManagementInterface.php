<?php
declare(strict_types=1);

namespace ExperiencesDigital\Flights\Api;

interface FlightsManagementInterface
{
    

    /**
     * GET for flights api
     * @param string $flight_no
     * @param string $flight_date
     * @return string
     */
    public function getFlights($flight_no,$flight_date);
}

