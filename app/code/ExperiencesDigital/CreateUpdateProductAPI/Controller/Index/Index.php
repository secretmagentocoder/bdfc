<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace ExperiencesDigital\CreateUpdateProductAPI\Controller\Index;

use Exception;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreFactory;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $resourceConnection;

    protected $exciseCategories;
    protected $objectManager;
    protected $apiProdouctData;
    protected $lastDbproductId;
    protected $sku;
    protected $productFactory;
    protected $customAttributes;

    private $navConfigProvider;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProductFactory $productFactory,
        ConfigProvider $navConfigProvider
    ) {

        $this->resultPageFactory = $resultPageFactory;
        $this->productFactory = $productFactory;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->resourceConnection = $this->objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $this->navConfigProvider = $navConfigProvider;
        $this->exciseCategories = [];
        $this->setCustomAttributes();
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $this->sku = $_GET['sku'] ?? '';
        $start_time = microtime(true);
        $this->setExciseCategories();
        $this->createOrUpdateProduct($skip = 0, $limit = 1000);
        $end_time = microtime(true);
        // Calculate script execution time
        $execution_time = ($end_time - $start_time);
        echo " Proccessed Products Data in  = " . $execution_time . " sec</br>";
    }

    private function setCustomAttributes()
    {
        $this->customAttributes = [
            'web_minimum_quantity' => 'Web_Minimum_Quantity',
            'custom_allowence_category' => 'Custom_Category',
            'qty_per_custom_uom' => 'Qty_Per_Custom_UOM',
            'web_pre_order_allowed' => 'Web_Pre_Order_Allowed',
            'web_maximum_quantity' => 'Web_Maximum_Quantity',
            'gift_wrapping_available' => 'Web_Gift_Wrap_Allowed',
            'Branding_Code' => 'Branding_Code',
            'pr_cartreservation_enable' => 'Web_Minimum_Quantity',

            'division' => 'Division_Code',
            'product_group' => 'Product_Group_Code',
            'brand_owner' => 'Brand_Owner_Code',
            'brand_code' => 'Brand_Code',
            'sub_brand_code' => 'Sub_Brand_Code'
        ];
    }

    private function setExciseCategories()
    {
        $start_time = microtime(true);
        $categoriesData = $this->curlCallExciseCategory();
        foreach ($categoriesData as $category) {
            if (isset(($category['Code']))) {
                array_push($this->exciseCategories, $category['Code']);
            }
        }
        $end_time = microtime(true);
        // Calculate script execution time
        $execution_time = ($end_time - $start_time);
        echo " Proccessed Excise Categories in  = " . $execution_time . " sec</br>";
        return  $this->exciseCategories;
    }

    private function createOrUpdateProduct($skip, $limit)
    {


        $productData = $this->curlCall($skip, $limit);
        $this->processProductData($productData);
        if ($this->sku != '') {
            if (count($productData) >= $limit) {
                $skip = $skip + $limit;
                echo "Procees Next Set";
                $this->createOrUpdateProduct($skip, $limit);
            } else {
                return;
            }
        }
    }

    private function processProductData($productData)
    {

        ob_start();
        if (is_array($productData) && count($productData) > 0) {

            foreach ($productData as $product) {
                $dbProductId = $this->__isExistProduct($product['No']);
                if ($dbProductId) {
                    $this->lastDbproductId = $dbProductId;

                    $this->updateProduct($product);
                    echo "updating SKU with NO " . $product['No'] . "<br>";
                } else {
                    $this->lastDbproductId = '';

                    $this->createProduct($product);
                    echo "creating SKU with NO " . $product['No'] . "<br>";
                }

                flush();
                ob_flush();
            }
        }
    }

    private function __isExistProduct($sku)
    {

        return $this->objectManager->get("Magento\Catalog\Model\Product")->getIdBySku($sku);;
    }

    private function createProduct($product_data)
    {
        //Create Product

    }
    private function updateProduct($product_data)
    {

        if (!empty($product_data)) {
            $this->apiProdouctData = $product_data;
            $exciseArray = $this->getExciseApplicablePrice();

            $sku = $product_data['No'];
            $product = $this->productFactory->create();

            // Update via product factory
            try {

                $productId = $product->getIdBySku($sku);
                $brandName = $product_data['Brand_Description'];
                $brandId = $this->getBrandIdFromName($brandName, $productId);

                $product->load($product->getIdBySku($sku)); //Load product
                //Set Global Attributes for all stores
                foreach ($this->customAttributes as $key => $attribute) {
                    if (isset($product_data[$attribute])) {
                        $product->setCustomAttribute($key, $product_data[$attribute]);
                    }
                }

                $product->setCustomAttribute('product_brand', $brandId);
                //Global attribuet for giftwrap and timelime
                $product->setCustomAttribute('use_config_gift_wrapping_available', 1);

                if ($product_data['Web_Cart_Time_Limit_in_Minutes'] > 0) {
                    $product->setCustomAttribute('pr_cartreservation_enable', 1);
                } else {
                    $product->setCustomAttribute('pr_cartreservation_enable', "0");
                }
                // $product->setName("TEST Camacho Corojo Toro Cello 20'S");//For Name of the product
                if (isset($product_data['Master_Product']) && $product_data['Master_Product'] != '') {

                    $product->setCustomAttribute('redirect_to_configurable_product', "1");
                }

                // For excise 
                if ($exciseArray['excise_applicable'] == 1) {
                    $product->addAttributeUpdate('price', $exciseArray['unit_price'], 2);
                }

                // $product->addAttributeUpdate('tax_class_id', 13, 2); //Arrival Tax clas
                // $product->addAttributeUpdate('tax_class_id', 0, 3); //Departure Tax class
                return $product->save();
            } catch (Exception $e) {
            }
        }
    }

    private function getBrandIdFromName($brandName, $productId)
    {
        $brandId = '';

        $magetopBrand = $this->resourceConnection->getTableName('magetop_brand');


        $getBrandId = "Select brand_id FROM " . $magetopBrand . " where name = '" . $brandName . "'";
        $result = $this->resourceConnection->fetchAll($getBrandId);


        if ($result) {
            $brandId =  $result[0]['brand_id'];
        }
        $this->updateBrandInMagetop($brandId, $productId);
        return $brandId;
    }

    private function updateBrandInMagetop($brandId, $productId)
    {

        $tableName2 = $this->resourceConnection->getTableName('magetop_brand_product');
        $sqlDelete = "Delete FROM " . $tableName2 . " Where product_id = $productId && brand_id = $brandId";
        $this->resourceConnection->query($sqlDelete);
        if (!empty($brandId)) {
            $sql = "insert into " . $tableName2 . " (brand_id, product_id, position) Values ($brandId, $productId, 0)";
            $this->resourceConnection->query($sql);
        }
    }

    private function getExciseApplicablePrice()
    {

        $unitPrice = 0;
        $exciseApplicable = 0;

        if (!empty($this->lastDbproductId)) {
            $product = $this->objectManager->get('Magento\Catalog\Model\Product')->load($this->lastDbproductId);
            $unitPrice = $product->getUnitPrice();
        }

        if (isset($this->apiProdouctData['Product_Group_Code']) && in_array($this->apiProdouctData['Product_Group_Code'], $this->exciseCategories)) {

            $unitPrice = 2 * $unitPrice;
            $exciseApplicable = 1;

            //Excise Applicable on this item
            //Get Unit price and save double of it in same sku
        }
        return ['unit_price' => $unitPrice, 'excise_applicable' => $exciseApplicable];
    }

    //Call Product Odata API with skip and limit

    private function curlCall($skip, $limit)
    {
        $sku = $this->sku;
        if (!empty($sku)) {
            $sku = "'" . $sku . "'";
            $store_url = 'WebItemList?$format=application/json&$filter=No%20eq%20' . $sku . '&$skip=' . $skip . '&$top=' . $limit;
        } else {
            $store_url = 'WebItemList?$format=application/json&$skip=' . $skip . '&$top=' . $limit;;
        }

        return $this->callCurlBasedOnURL($store_url);
    }

    private function curlCallExciseCategory()
    {
        return $this->callCurlBasedOnURL('WebRetailProduct?$format=application/json&$filter=Excise_Applicable%20eq%20true');
    }

    private function callCurlBasedOnURL($url)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $store_url = $host.'/Company(%27'.$company.'%27)/' . $url;
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

    // End
    private function baseURL()
    {

        $storeManager = $this->objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        return $storeManager->getStore()->getBaseUrl();
    }
}
