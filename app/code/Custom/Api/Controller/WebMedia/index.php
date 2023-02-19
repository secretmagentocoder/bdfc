<?php

namespace Custom\Api\Controller\WebMedia;

use Exception;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Store\Model\WebsiteFactory;
use Magento\Store\Model\StoreFactory;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultJsonFactory;
    protected $resourceConnection;
    protected $eavSetupFactory;
    protected $storeManager;
    protected $websiteFactory;
    protected $storeFactory;
    protected $categoryFactory;
    protected $objectManager;
    private $skuImages;
    private $navConfigProvider;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resourceConnection,
        WebsiteFactory $websiteFactory,
        StoreFactory $storeFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ConfigProvider $navConfigProvider,
        array $data = array()
    ) {
        $resultJsonFactory = $resultJsonFactory;
        $resourceConnection = $resourceConnection;
        $this->websiteFactory = $websiteFactory;
        $this->storeFactory = $storeFactory;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->navConfigProvider = $navConfigProvider;
        $this->skuImages = [];
        parent::__construct($context);
    }

    private function getMediaData()
    {
        $sku = $_GET['sku'] ?? '';
        if (empty($sku)) {
            echo "SKU needed for Image API";
            die;
        }
        $this->removeImagesSku($sku);


        $images = $this->curlCall(0, 10, $sku);
        $this->processImagesData($images);
    }

    private function processImagesData($images)
    {
        if (count($images) > 0) {
            foreach ($images as $key => $value) {
                ob_flush();
                if(isset($this->skuImages[$key])){
                    //Exist Image
                    $this->updateProductImage($value, $this->skuImages[$key]);
                }else{
                    // $this->addProductImage($value);
                }
               
                 
            }
        }
    }

    private function removeImagesSku($sku)
    {



        echo "Image fetching for product with SKU" . $sku . "</br>";
        try {
            $product = $this->objectManager->create('Magento\Catalog\Model\ProductRepository')->get($sku);
            $productGallery = $this->objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Gallery');
            $gallery = $product->getMediaGalleryImages();

            if (count($gallery) > 0) {
                foreach ($gallery as $image) {
                    $this->skuImages[] = $image->getValueId();
                }
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        }
    }
    

    private function updateProductImage($image,$image_id)
    {

        echo "Image procesing for product with SKU" . $image['Primary_Key'] . "</br>";
        
        echo $imageURL = $this->getImageURL($image['Image_Pathway'] . '' . $image['Image_Name']);
        if (!empty($imageURL)) {
            //Add image to productt
            $productID = $image['Primary_Key'];
            $imageFormat = $this->get_image_mime_type($imageURL);
            $b64image = base64_encode(file_get_contents($imageURL));
            // // Post image to magento 
            // $ch = curl_init("https://bdfc.experiences.digital/index.php/rest/V1/products/$productID/media");
            // # Setup request to send json via POST.

            // $payload = '{
            //     "entry": {
            //         "mediaType": "image",
            //         "position": 1,
                   
            //         "disabled": false,
            //         "types": ["image", "small_image", "thumbnail"],
            //         "content":{
            //             "base64_encoded_data" : "' . $b64image . '",
            //             "type": "' . $imageFormat . '",
            //             "name":"' . $image['Image_Name'] . '"
                    
            //         }
            //     }
            //     }';
            // echo $payload;
            // curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            // $headers = array(
            //     "Accept: application/json",
            //     'Content-Type:application/json',
            //     "Authorization: Bearer xwpwzi25clerlcxlxblaxlk8tvwvwkx6",
            // );
            // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // # Return response instead of printing.
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // # Send request.
            // $result = curl_exec($ch);
            // curl_close($ch);
            // # Print response.
            // echo "<pre>$result</pre>";
            // $result_data = json_decode($result);






            // // 
           
            $payload2 = '{
                "entry":{
                    "id":"' . $image_id . '",
                    "media_type":"image",
                    "label":"",
                    "position":1,
                    "disabled":false,
                    "types":[
                        "image",
                        "small_image",
                        "thumbnail"
                    ],
                     "content":{
                        "base64_encoded_data" : "' . $b64image . '",
                        "type": "' . $imageFormat . '",
                        "name":"' . $image['Image_Name'] . '"
                    
                    }
                }
                }';

            //
            $baseUrl = $this->navConfigProvider->getBaseUrl();
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $baseUrl.'index.php/rest/all/V1/products/' . $productID . '/media/' . $image_id,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => $payload2,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer xwpwzi25clerlcxlxblaxlk8tvwvwkx6',
                    'Content-Type: application/json',
                    'Cookie: PHPSESSID=aadhosq2qglb14j5sbcgsv4b7c'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            echo $response;
        }
    }

    private function processCategoryImage($image)
    {
        echo "category";
    }

    private function get_image_mime_type($image_path)
    {
        $mimes  = array(
            IMAGETYPE_GIF => "image/gif",
            IMAGETYPE_JPEG => "image/jpeg",
            IMAGETYPE_PNG => "image/png",
            IMAGETYPE_SWF => "image/swf",
            IMAGETYPE_PSD => "image/psd",
            IMAGETYPE_BMP => "image/bmp",
            IMAGETYPE_TIFF_II => "image/tiff",
            IMAGETYPE_TIFF_MM => "image/tiff",
            IMAGETYPE_JPC => "image/jpc",
            IMAGETYPE_JP2 => "image/jp2",
            IMAGETYPE_JPX => "image/jpx",
            IMAGETYPE_JB2 => "image/jb2",
            IMAGETYPE_SWC => "image/swc",
            IMAGETYPE_IFF => "image/iff",
            IMAGETYPE_WBMP => "image/wbmp",
            IMAGETYPE_XBM => "image/xbm",
            IMAGETYPE_ICO => "image/ico"
        );

        if (($image_type = exif_imagetype($image_path))
            && (array_key_exists($image_type, $mimes))
        ) {
            return $mimes[$image_type];
        } else {
            return FALSE;
        }
    }

    private function getImageURL($imageURL)
    {
        if ($this->url_exists($imageURL . '.jpg')) {
            return $imageURL . '.jpg';
        } elseif ($this->url_exists($imageURL . '.jpeg')) {
            return $imageURL . '.jpeg';
        } elseif ($this->url_exists($imageURL . '.png')) {
            return $imageURL . '.png';
        } else {
            return '';
        }
    }

    private function url_exists($url)
    {
        if (@getimagesize($url)) {
            return true;
        } else {
            return false;
        }
    }

    private function curlCall($skip=0, $limit=10, $sku)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $sku = "'" . $sku . "'";
        $store_url = $host.'/Company(%27'.$company.'%27)/WebMedia?$format=application/json&$orderby=Line_No%20desc&$filter=Table_No%20eq%2027%20and%20Primary_Key%20eq%20' . $sku . '&$skip=' . $skip . '&$top=' . $limit;
        

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

    public function execute()
    {
        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/api_website_media.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        $this->getMediaData();
    }
}
