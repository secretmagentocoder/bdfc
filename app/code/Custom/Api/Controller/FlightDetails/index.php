<?php


namespace Custom\Api\Controller\FlightDetails;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreFactory;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;

    protected $resourceConnection;

    protected $eavSetupFactory;

    protected $storeFactory;

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
        $flightdetails='';
        $flight_date=$_GET['flight_date']??'';
        $flight_no=$_GET['flight_no']??'';
        if(!empty($flight_date) && !empty($flight_no)){
            $flight_date = str_replace('/', '-', $flight_date);
            $flight_d=date('Y-m-d',strtotime($flight_date));
         
          $flightdetails = $this->curlCall($flight_d,$flight_no); 
          
        }
        
        echo json_encode($flightdetails);
       
    }

    private function curlCall($flight_date, $flight_no)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $flight_date = "'" . $flight_date . "'";
        $flight_no = "'" . $flight_no . "'";
        $store_url = $host.'/Company(%27'.$company.'%27)/FlightSchedules?$format=application/json&$filter=Flight_Date%20eq%20DateTime'.$flight_date. '%20and%20FNO%20eq%20'.$flight_no;;
        
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
        curl_close($curl);
        $response = json_decode($response_data, true);

        if (is_array($response) && count($response) > 0 && isset($response['value'][0])) {
            return $response['value'][0];
        } else {
            return [];
        }
    }

}
