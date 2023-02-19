<?php


namespace Custom\Api\Controller\ProductChangeLog;

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

        $this->callProductAPIs(0,100);
    }


    //recursively category api call
    private function callProductAPIs($skip, $limit)
    {


        $productLogs = $this->curlCall($skip, $limit);
        $this->processProductData($productLogs);
        // if (count($productLogs) >= $limit) {
        //     $skip = $skip + $limit;
        //     echo "Procees Next Set";
        //     $this->callProductAPIs($skip, $limit);
        // } else {
        //     exit;
        // }
    }

    private function processProductData($productLogs)
    {
       
        ob_start();
        if (is_array($productLogs) && count($productLogs) > 0) {

            foreach ($productLogs as $product) {

                echo "processing SKU with NO " . $product['Item_No'];
                $this->callAllProductAPIs($product['Item_No']);
                usleep(100);
                flush();
                ob_flush();
            }
        }
    }

    private function callAllProductAPIs($sku)
    {
        $baseUrl = $this->navConfigProvider->getBaseUrl();
        echo $sku;
        echo  file_get_contents($baseUrl.'customapi/webmedia/index?sku='. $sku);
    }


    private function curlCall($skip, $limit)
    {
        $date=isset($_GET['date'])? $_GET['date']:date('Y-m-d');
        $date = "'" . $date . "'";
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $store_url = $host.'/Company(%27'.$company.'%27)/ItemChangeLogEntry?$format=application/json&$filter=Modified_Date%20eq%20DateTime'. $date.'&$skip=' . $skip . '&$top=' . $limit;;
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

        if (is_array($response) && count($response) > 0) {
            return $response['value'];
        } else {
            return [];
        }
    }
}
