<?php
 

namespace Custom\Api\Controller\ProductAttributeUpdate;

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

        $this->updateProductCustomAttribute($skip = 0, $limit = 500);
    }


    //recursively category api call
    private function updateProductCustomAttribute($skip, $limit)
    {

        
        $productData = $this->curlCall($skip, $limit);
        $this->processProductData($productData);
        if (count($productData) >= $limit) {
            $skip = $skip + $limit;
            echo "Procees Next Set";
            $this->updateProductCustomAttribute($skip, $limit);
        }else{
            exit;
        }
    }

    private function processProductData($productData)
    {
        ob_start();
        if (is_array($productData) && count($productData) > 0) {
            
            foreach ($productData as $product) {
                
                echo "processing SKU with NO ". $product['No'];
                $this->updateProductData($product);
                usleep(100);
                flush();
                ob_flush();
            }
        }
    }

    private function updateProductData($product_data)
    {
        $pr_cartreservation_enable="0";
        if (!empty($product_data)) {

            $sku = $product_data['No'];
            $baseUrl = $this->navConfigProvider->getBaseUrl();
            $ch = curl_init($baseUrl."index.php/rest/all/V1/products/" . $sku);
            # Setup request to send json via POST.
            $web_minimum_quantity = $product_data['Web_Minimum_Quantity'];
            $web_maximum_quantity = $product_data['Web_Maximum_Quantity'];
            $web_gift_wrap_allowed = $product_data['Web_Gift_Wrap_Allowed'];
            $web_pre_order_allowed = (int)$product_data['Web_Pre_Order_Allowed'];
            $branding_code = $product_data['Branding_Code'];
            $web_cart_time_limit_in_minutes = $product_data['Web_Cart_Time_Limit_in_Minutes'];
            $division = $product_data['Division_Code'];
            $product_group = $product_data['Product_Group_Code'];
            $brand_owner = $product_data['Brand_Owner_Code'];
            $brand_code = $product_data['Brand_Code'];
            $sub_brand_code = $product_data['Sub_Brand_Code'];
            if($web_cart_time_limit_in_minutes>0){
                $pr_cartreservation_enable="1";
            }
            $payload = '{
                "product": {
                    "sku": "' . $sku . '",
                    "attribute_set_id": 4,
                    
                    
                   "custom_attributes": [
                    {
                        "attribute_code":"use_config_gift_wrapping_available",
                        "value":"1"
                    },
                     {
                        "attribute_code":"product_brand",
                        "value":"3"
                    },
                    
                    {
                        "attribute_code": "web_minimum_quantity",
                        "value": "' . $web_minimum_quantity . '"
                    },{
                        "attribute_code": "web_pre_order_allowed",
                        "value": "' . $web_pre_order_allowed . '"
                    },{
                        "attribute_code": "web_maximum_quantity",
                        "value": "' . $web_maximum_quantity . '"
                    },{
                        "attribute_code": "gift_wrapping_available",
                        "value": "' . $web_gift_wrap_allowed . '"
                    },{
                        "attribute_code": "Branding_Code",
                        "value": "' . $branding_code . '"
                    },{
                        "attribute_code": "pr_cartreservation_enable",
                        "value": "' . $pr_cartreservation_enable . '"
                    },{
                        "attribute_code": "web_cart_time_limit_in_minutes",
                        "value": "' . $web_cart_time_limit_in_minutes . '"
                    },{
                        "attribute_code": "division",
                        "value": "' . $division . '"
                    },{
                        "attribute_code": "product_group",
                        "value": "' . $product_group . '"
                    },{
                        "attribute_code": "brand_owner",
                        "value": "' . $brand_owner . '"
                    },{
                        "attribute_code": "brand_code",
                        "value": "' . $brand_code . '"
                    },{
                        "attribute_code": "sub_brand_code",
                        "value": "' . $sub_brand_code . '"
                    }
                    ]
                }
                }';
            //echo $payload;

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
            echo "<pre>Done Proceesing </pre>";
        }
    }


    private function curlCall($skip, $limit)
    {
        $sku=isset($_GET['sku'])?$_GET['sku']:'';
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        if (!empty($sku)) {
            $sku = "'" . $sku . "'";
            $store_url = $host.'/Company(%27'.$company.'%27)/WebItemList?$format=application/json&$filter=No%20eq%20' . $sku . '&$skip=' . $skip . '&$top=' . $limit;
        }else{
            $store_url = $host.'/Company(%27'.$company.'%27)/WebItemList?$format=application/json&$skip=' . $skip . '&$top=' . $limit;;
        }
        echo $store_url;
        
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
