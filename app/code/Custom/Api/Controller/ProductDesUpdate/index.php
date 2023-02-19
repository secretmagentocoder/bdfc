<?php


namespace Custom\Api\Controller\ProductDesUpdate;

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
        $item_code=isset($_GET['item_code'])? $_GET['item_code']:"";

        $this->updateProductDesc($skip = 0, $limit = 50, $item_code);
    }


    //recursively category api call
    private function updateProductDesc($skip, $limit, $item_code)
    {

        echo "<pre>";
       
        $productData = $this->curlCall($skip, $limit, $item_code);
        $this->processProductData($productData);
        if (empty($item_code) && count($productData) >= $limit) {
            $skip = $skip + $limit;
            echo "Procees Next Set";
            $this->updateProductDesc($skip, $limit, $item_code);
        } else {
            exit;
        }
    }

    private function processProductData($productData)
    {
        ob_start();
        if (is_array($productData) && count($productData) > 0) {

            foreach ($productData as $product) {

                echo "processing SKU with NO " . $product['Item_No'];
                $this->updateProductData($product);
                usleep(100);
                flush();
                ob_flush();
            }
        }
    }

    private function updateProductData($product_data)
    {
       
        if (!empty($product_data)) {

            $sku = $product_data['Item_No'];
            // $inventory= $this->callInventoryAPI($sku);
            // die;
            // Post image to magento 
            $baseUrl = $this->navConfigProvider->getBaseUrl();
            $ch = curl_init($baseUrl."index.php/rest/all/V1/products/" . $sku);
            # Setup request to send json via POST.
            $short_desc = $product_data['Web_Short_Desc_1'];
            $long_desc = str_replace('"', "'", $product_data['Txt']);
            $short_desc = str_replace('"', "'", $short_desc);
            
            


            $payload = '{
                "product": {
                    "sku": "' . $sku . '",
                    "name":"' . $short_desc . '",
                    "attribute_set_id": 4,
                   
                    "custom_attributes": [
                    {
                        "attribute_code": "short_description",
                        "value": "' . $long_desc . '"
                    }
                    ]
                }
                }';
            echo $payload;

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $headers = array(
                "Accept: application/json",
                'Content-Type:application/json',
                "Authorization: Bearer xwpwzi25clerlcxlxblaxlk8tvwvwkx6",
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            # Return response instead of printing.
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            # Send request.
            $result = curl_exec($ch);
            curl_close($ch);
            # Print response.
            echo "<pre>Done Proceesing SKU</pre>". $sku ."with" . $short_desc;
        }
    }


    private function curlCall($skip, $limit,$sku)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        if(!empty($sku)){
            $sku = "'" . $sku . "'";
            $store_url = $host.'/Company(%27'.$company.'%27)/WebItemInfoList?$format=application/json&$filter=Item_No%20eq%20' . $sku . '&$skip=' . $skip . '&$top=' . $limit;
        }else{
            $store_url = $host.'/Company(%27'.$company.'%27)/WebItemInfoList?$format=application/json&$skip=' . $skip . '&$top=' . $limit;;
        }
        
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
