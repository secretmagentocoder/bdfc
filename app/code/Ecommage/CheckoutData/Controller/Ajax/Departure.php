<?php
namespace Ecommage\CheckoutData\Controller\Ajax;

use Bodak\CheckoutCustomForm\Api\Data\CustomFieldsInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class Departure extends Action
{
    /**
     * @var \Ecommage\CheckoutData\Helper\Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_date;

    /**
     * @var StoreManager
     */
    private $storeManager;

    protected $checkoutSession;

    private $navConfigProvider;

    public function __construct(Context $context,
                                \Ecommage\CheckoutData\Helper\Data  $helper,
                                LoggerInterface $logger,
                                ResultFactory $resultFactory,
                                \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
                                \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
                                StoreManager $storeManager,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                ConfigProvider $navConfigProvider

    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->_date = $date;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->resultFactory = $resultFactory;
        $this->_logger = $logger;
        $this->helper = $helper;
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        try{
            $dateModel = $this->dateTimeFactory->create();
            $currentStore = $this->storeManager->getStore()->getCode();
            if($currentStore == 'arrival'){
                $this->setArrivalFlightDateQuote($this->getRequest()->getParam('arrivalDate'));
                $flightDate = $dateModel->gmtDate('Y-m-d',$this->getRequest()->getParam('arrivalDate'));
            }
            if($currentStore == 'departure'){
                $flightDate = $dateModel->gmtDate('Y-m-d',$this->getRequest()->getParam('departureDate'));
            }
            $flightNo = $this->getRequest()->getParam('flightNo');
            $apiAirline = $this->getApiAirlineList();

//            $airlineList = $this->helper->curlCall($apiAirline);
            $airlineDetail = $this->curlCall($flightDate,$flightNo);
            $dataResponse = [];
            if($airlineDetail){
                $dataResponse = [
                    'airline_logo'=>$airlineDetail[0]['Airline_Image_URL'],
                    'airline_name'=>($airlineDetail[0]['Name']) ? $airlineDetail[0]['Name'] : $airlineDetail[0]['Airline_Name'],
                    'departure_time'=>$airlineDetail[0]['Flight_Time'],
                    'arrival_time'=>$airlineDetail[0]['Flight_Time'],
                    'destination'=>$airlineDetail[0]['Destination_Name'],
                    'origin'=>$airlineDetail[0]['Origin_Name'],
                ];
                $this->checkoutSession->setFligtDetails($airlineDetail);
            }

            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            return $resultJson->setData($dataResponse);
        }catch(\Exception $e){
            $this->_logger->error($e->getMessage());
        }
    }

    protected function setArrivalFlightDateQuote ($date){
        try{
            $quote = $this->checkoutSession->getQuote();
            $quote->setData(CustomFieldsInterface::ARRIVAL_FLIGHT_DATE, $date);
            $quote->save();
        }catch(\Exception $e){
            $this->_logger->error($e->getMessage());
        }
    }

    public function getApiAirlineDetailLink($flightNo,$flightDate)
    {
        $base = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_DIRECT_LINK);
        return  sprintf('%srest/V1/experiencesdigital/flights?flight_no=%s&flight_date=%s',$base,$flightNo,$flightDate);
    }

    public function getApiAirlineList()
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        return $host.'/Company(%27'.$company.'%27)/Airlinelist?$format=application/json';
    }

    protected function curlCall($flight_date, $flight_no)
    {
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $store_url = $this->getApiAirlineDetailLink($flight_no,$flight_date);
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
        if (is_array($response)) {
            return $response;
        } else {
            return [];
        }
    }

}
