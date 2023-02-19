<?php

namespace Custom\Api\Controller\Offer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Custom\Api\Model\Offerheader;
use Custom\Api\Model\Offerline;
// use Custom\Api\Model\Model\ResourceModel\Offerheader as OfferheaderResource;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class InsertData extends \Magento\Framework\App\Action\Action {

    protected $resultJsonFactory;
    protected $resourceConnection;
    protected $eavSetupFactory;
    protected $_coupon;
    protected $storeManager;
    protected $objectManager;
    protected $offerheader;
    protected $offerline;
    private $navConfigProvider;

    public function __construct(
            Context $context,
            JsonFactory $resultJsonFactory,
            ResourceConnection $resourceConnection,
            ObjectManagerInterface $objectmanager,
            StoreManagerInterface $storeManager,
            \Magento\SalesRule\Model\Coupon $coupon,
            Offerheader $offerheaderFactory,
            Offerline $offerlineFactory,
            ConfigProvider $navConfigProvider,
            array $data = array()
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        // $this->eavSetupFactory = $eavSetupFactory;
        $this->storeManager = $storeManager;
        $this->objectManager = $objectmanager;
        $this->_coupon = $coupon;
        $this->offerheaderFactory = $offerheaderFactory; 
        $this->offerlineFactory = $offerlineFactory;
        $this->navConfigProvider = $navConfigProvider; 
        parent::__construct($context);
    }

    public function execute() 
    {
        //Web Offer Line
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $offers = $host.'/Company(%27'.$company.'%27)/WebOfferLine?$format=application/json&$top=100';
        $curl1 = curl_init();
        curl_setopt_array($curl1, array(
           CURLOPT_URL => $offers,
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
       $responseData = curl_exec($curl1);
       curl_close($curl1);
    //    echo '<pre>';
       $responseArray1 = json_decode($responseData, TRUE);
        foreach($responseArray1['value'] as $responseArray)
        {

            $data['offer_no'] = $responseArray['Offer_No'];
            $data['line_no'] = $responseArray['Line_No'];
            $data['type'] = $responseArray['Type'];
            $data['description'] = $responseArray['Description'];
            $data['standard_price_including_vat'] = $responseArray['Standard_Price_Including_VAT'];
            $data['standard_price'] = $responseArray['Standard_Price'];
            $data['deal_price_disc_perc'] = $responseArray['Deal_Price_Disc_Perc'];
            $data['price_group'] = $responseArray['Price_Group'];
            $data['currency_code'] = $responseArray['Currency_Code'];
            $data['unit_of_measure'] = $responseArray['Unit_of_Measure'];
            $data['prod_group_category'] = $responseArray['Prod_Group_Category'];
            $data['line_group'] = $responseArray['Line_Group'];
            $data['no_of_items_needed'] = $responseArray['No_of_Items_Needed'];
            $data['disc_type'] = $responseArray['Disc_Type'];
            $data['discount_amount'] = $responseArray['Discount_Amount'];
            $data['offer_price'] = $responseArray['Offer_Price'];
            $data['offer_price_including_vat'] = $responseArray['Offer_Price_Including_VAT'];
            $data['discount_amount_including_vat'] = $responseArray['Discount_Amount_Including_VAT'];
            $data['status'] = $responseArray['Status'];
            $data['offer_desc'] = $responseArray['Offer_Desc'];
            $data['fixed_quantity_to_sell'] = $responseArray['Fixed_Quantity_to_Sell'];
            $data['exclude'] = $responseArray['Exclude'];
            $this->offerlineFactory->setData($data)->save();
        }


        $offer_url = $host.'/Company('.'\''.$company.'\''.')/WebOfferHeader?$format=application/json&$top=100';
        $curl2 = curl_init();
        curl_setopt_array($curl2, array(
            CURLOPT_URL => $offer_url,
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
        $response_data = curl_exec($curl2);
        curl_close($curl2);
        echo '<pre>';
        $response_array1 = json_decode($response_data, TRUE);
        foreach($response_array1['value'] as $response_array)
        {
            $data['no'] = $response_array['No'];
            $data['description'] = $response_array['Description'];
            $data['status'] = $response_array['Status'];
            $data['type'] = $response_array['Type'];
            $data['price_group'] = $response_array['Price_Group'];
            $data['offer_type'] = $response_array['Offer_Type'];
            $data['priority'] = $response_array['Priority'];
            $data['currency_code'] = $response_array['Currency_Code'];
            $data['validation_period_id'] = $response_array['Validation_Period_ID'];
            $data['validation_description'] = $response_array['Validation_Description'];
            $data['starting_date'] = $response_array['Starting_Date'];
            $data['ending_date'] = $response_array['Ending_Date'];
            $data['use_trans_line_time'] = $response_array['Use_Trans_Line_Time'];
            $data['price_group_filter'] = $response_array['Price_Group_Filter'];
            $data['disc_validation_period_filter'] = $response_array['Disc_Validation_Period_Filter'];
            $data['block_periodic_discount'] = $response_array['Block_Periodic_Discount'];
            $data['price_group_validation'] = $response_array['Price_Group_Validation'];
            $data['sales_type_filter'] = $response_array['Sales_Type_Filter'];
            $data['discount_type'] = $response_array['Discount_Type'];
            $data['same_diff_m_x0026_m_lines'] = $response_array['Same_Diff_M_x0026_M_Lines'];
            $data['no_of_lines_to_trigger'] = $response_array['No_of_Lines_to_Trigger'];
            $data['deal_price_value'] = $response_array['Deal_Price_Value'];
            $data['discount_perc_value'] = $response_array['Discount_Perc_Value'];
            $data['discount_amount_value'] = $response_array['Discount_Amount_Value'];
            $data['no_of_least_expensive_items'] = $response_array['No_of_Least_Expensive_Items'];
            $data['disc_perc_of_least_expensive'] = $response_array['Disc_Perc_of_Least_Expensive'];
            $data['no_of_times_applicable'] = $response_array['No_of_Times_Applicable'];
            $data['no_of_line_groups'] = $response_array['No_of_Line_Groups'];
            $data['customer_disc_group'] = $response_array['Customer_Disc_Group'];
            $data['amount_to_trigger'] = $response_array['Amount_to_Trigger'];
            $data['member_value'] = $response_array['Member_Value'];
            $data['last_date_modified'] = $response_array['Last_Date_Modified'];
            $data['coupon_code'] = $response_array['Coupon_Code'];
            $data['web_offer'] = $response_array['Web_Offer'];
            
            $this->offerheaderFactory->setData($data)->save();
        
        // echo "<pre>";
        // print_r($this->offerheaderFactory->getData());
        // die;
        
        }

        // if($this->offerheaderFactory->getId()){
        //     echo $this->offerheaderFactory->get();
        // }
    }
}


