<?php

declare(strict_types=1);

namespace ExperiencesDigital\Flights\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Store\Model\StoreFactory;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;

    protected $resourceConnection;

    protected $eavSetupFactory;

    protected $storeFactory;
    protected $flightsTableName = "flights";
    protected $airlineTableName = "airlines";

    private $navConfigProvider;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        StoreFactory $storeFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        ConfigProvider $navConfigProvider,
        array $data = array()
    ) {
        $resultJsonFactory = $resultJsonFactory;
        $this->timezone = $timezone;
        $this->categoryFactory = $categoryFactory;
        $this->storeFactory = $storeFactory;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->resourceConnection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        $start_time = microtime(true);
        $this->insertAirlinesLogo();
        $this->recursivelyAPICall(0);
        // End clock time in seconds
        $end_time = microtime(true);
        // Calculate script execution time
        $execution_time = ($end_time - $start_time);

        echo " Proccessed Flights Data in  = " . $execution_time . " sec</br>";
        
    }

    private function recursivelyAPICall($offset)
    {
        $top = 1000;
        $flightData = $this->curlCall($offset, $top);
        if (count($flightData) >= $top) {
            $newOffset = $offset + $top;
            $this->processFlightsData($flightData);

            $this->recursivelyAPICall($newOffset);
            echo "Next set of flight data</br>";
        } else {
            $this->processFlightsData($flightData);
            echo "Last set of flight data</br>";
            
        }
    }





    private function processFlightsData($flightsData)
    {


        if (is_array($flightsData) && count($flightsData) > 0) {
            foreach ($flightsData as $flight) {
                if ($this->resourceConnection->isTableExists($this->flightsTableName) == true) {

                    $flghtData = [
                        'Airline' => $flight['Airline'],
                        'FNO' => $flight['FNO'],
                        'Flight_Date' => $flight['Flight_Date'],
                        'Flight_Time' => $flight['Flight_Time'],
                        'FTYPE' => $flight['FTYPE'],
                        'Airline_Name' => $flight['Airline_Name'],
                        'Origin_Code' => $flight['Origin_Code'],
                        'Origin_Name' => $flight['Origin_Name'],
                        'Destination_Code' => $flight['Destination_Code'],
                        'Destination_Name' => $flight['Destination_Name'],
                        'Gate' => $flight['Gate'],

                    ];

                    $where = ['FNO = ?' => $flight['FNO'], 'Flight_Date = ?' => $flight['Flight_Date'], 'Flight_Time = ?' => $flight['Flight_Time']];

                    $this->resourceConnection->delete($this->flightsTableName, $where);

                    $this->resourceConnection->insert($this->flightsTableName, $flghtData);
                }
                flush();
                ob_flush();
            }
        }
    }

    protected function insertAirlinesLogo()
    {

        $airlines = $this->curlCallForAirlines();

        if (is_array($airlines) && count($airlines) > 0) {
            foreach ($airlines as $airline) {

                if ($this->resourceConnection->isTableExists($this->airlineTableName) == true) {

                    $airlineData = [
                        'Code' => $airline['Code'],
                        'Name' => $airline['Name'],
                        'Airline_Image_URL' => $airline['Airline_Image_URL'],


                    ];
                    

                    $where = ['Code = ?' => $airline['Code']];

                    $this->resourceConnection->delete($this->airlineTableName, $where);

                    $this->resourceConnection->insert($this->airlineTableName, $airlineData);
                }
            }
        }
    }



    private function curlCall($skip, $top)
    {
        $yesterday = date('Y-m-d', strtotime("-1 Day"));
        $yesterday = "'" . $yesterday . "'";
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $store_url = $host.'/Company(%27'.$company.'%27)/FlightSchedules?$format=application/json&$filter=Flight_Date%20gt%20DateTime' . $yesterday . '&$skip=' . $skip . '&$top=' . $top;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $store_url,
            CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
            CURLOPT_USERPWD => $user.':'.$password,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response_data = curl_exec($curl);
        //var_dump($response_data);
        curl_close($curl);
        $response = json_decode($response_data, true);
        $delete = "DELETE FROM " . $this->flightsTableName . " where DATE(Flight_Date)<=Date(" . $yesterday . ")";
        $this->resourceConnection->query($delete);

        if (is_array($response) && count($response) > 0) {
            return $response['value'];
        } else {
            return [];
        }
    }

    private function curlCallForAirlines()
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $store_url = $host.'/Company(%27'.$company.'%27)/Airlinelist?$format=application/json&$top=1000&$filter=Airline_Image_URL%20ne%20\'\'';
        // echo $store_url;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $store_url,
            CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
            CURLOPT_USERPWD => $user.':'.$password,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response_data = curl_exec($curl);
        // var_dump($response_data);
        curl_close($curl);
        $response = json_decode($response_data, true);
        // print_r($response);

        if (is_array($response) && count($response) > 0) {

            return $response['value'];
        } else {
            return '';
        }
    }
}
