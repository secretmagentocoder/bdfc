<?php


namespace Custom\Api\Controller\ProductLimit;

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

    protected $objectManager;

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
        $this->objectManager=$objectManager;
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        $productRepository = $this->objectManager->get('\Magento\Catalog\Model\ProductRepository');
        $product = $productRepository->get('409643');
        $price = (float)$product->getData('unit_price');
        echo $price;

        // $this->updateProductCustomAttribute($skip = 0, $limit = 5000);
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
        } else {
            exit;
        }
    }

    private function processProductData($productData)
    {
        ob_start();
        if (is_array($productData) && count($productData) > 0) {

            foreach ($productData as $product) {

                echo "processing SKU with NO " . $product['No'];
                $this->updateProductData($product);
                usleep(100);
                flush();
                ob_flush();
            }
        }
    }

    private function updateProductData($product_data)
    {
        
        if (!empty($product_data) && $product_data['Web_Qty_Limit']>0) {
            $sku = $product_data['No'];
            $baseUrl = $this->navConfigProvider->getBaseUrl();
            $ch = curl_init($baseUrl."rest/V1/products/" . $sku . "/stockItems/1");
            # Setup request to send json via POST.
            $web_max_quantity = $product_data['Web_Qty_Limit'];
            


            $payload = '{
                "product_sku": "' . $sku . '",
                "stockItem": {
                    "use_config_max_sale_qty":0,
                    "max_sale_qty": ' . $web_max_quantity . '
                }
            }';


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
            var_dump($result);
            curl_close($ch);
            # Print response.
            echo "<pre>Done Proceesing </pre>";
        }
    }


    private function curlCall($skip, $limit)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $store_url = $host.'/Company(%27'.$company.'%27)/WebItemList?$format=application/json&$skip=' . $skip . '&$top=' . $limit;;
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
