<?php declare(strict_types=1);
/**
 * @package Ceymox_Navconnector
 */
namespace Ceymox\Navconnector\Model;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ceymox\Navconnector\Model\ConfigProvider;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory as AddressCollectionFactory;
use ExperiencesDigital\CustomCalculation\Model\ResourceModel\CustomCalculation\CollectionFactory as CalculationCollectionFactory;
/**
 * Class OrderCreateHandler
 *
 * Ceymox\Navconnector\Model
 */
class OrderCreateHandler
{
        /**
     * @var Curl
     */
    private $curl;

    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadata;

    /**
     * @var Data
     */
    private $jsonHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepositoryInterface;

    /**
     * OrderUpdate Nav constructor
     *
     * @param Curl $curl
     * @param CustomerRepositoryInterface $customerRepository
     * @param Data $jsonHelper
     * @param ConfigProvider $configProvider
     * @param AddressCollectionFactory $addressCollection
     * @param CalculationCollectionFactory $calculationCollection
     * @param RuleRepositoryInterface $ruleRepository
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
    	Curl $curl,
        CustomerRepositoryInterface $customerRepository,
        Data $jsonHelper,
        ConfigProvider $configProvider,
        AddressCollectionFactory  $addressCollection,
        CalculationCollectionFactory $calculationCollection,
        RuleRepositoryInterface $ruleRepository,
        ProductRepositoryInterface $productRepositoryInterface,
        OrderRepositoryInterface  $orderRepository
    ) {
    	$this->curl = $curl;
        $this->customerRepository = $customerRepository;
        $this->jsonHelper = $jsonHelper;
        $this->configProvider = $configProvider;
        $this->addressCollection = $addressCollection;
        $this->calculationCollection = $calculationCollection;
        $this->ruleRepository = $ruleRepository;
        $this->productRepositoryInterface  = $productRepositoryInterface;
        $this->orderRepository = $orderRepository;
    }

    public function processOrder(string $orderId)
    {
        try {            
            $order = $this->orderRepository->get((int)$orderId);
            if ($order->getId()) {               
                $xmlData = $this->createXmlData($order);
                $this->postRequest($xmlData);
            }
        } catch (\Exception $e) {            
            return;
        }
    }

    /**
     * Post Request
     *
     * @param string $response
     */
    public function postRequest($data)
    {
        $uri = $this->configProvider->getDeliveryDateApi();
        $userName = $this->configProvider->getUser();
        $password = $this->configProvider->getPassword();
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
            CURLOPT_USERPWD => $userName.':'.$password,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
        ];
        $this->curl->setOptions($options);
        $headers = ['SOAPAction: ""', "Content-Type" => "application/xml"];
        $this->curl->setHeaders($headers);
        $this->curl->post($uri, $data);
        
        $body = $this->curl->getBody();      
    }

    /**
     * Create xml format for api call
     *
     * @param object $order
     * @return string
     */
    public function createXmlData($order)
    {
        $customerId = 0;
        if (!$order->getCustomerIsGuest()){
            $customerId = $order->getCustomerId();
        }

        $customerPrefix = $order->getCustomerPrefix();
        $gender = '';
        if ($customerPrefix == 'Mr') {
            $gender = 'Male';
        } elseif ($customerPrefix == 'Mrs') {
            $gender = 'Female';
        }
        $dob = ($order->getCustomerDob()) ? $order->getCustomerDob() : $order->getCustomerCustDob();

        $fullMobileNumber = explode("-",$order->getMobileNumber());
        $countryCode = $fullMobileNumber[0];
        $mobileNumber = $fullMobileNumber[1];

        $coupenRuleName = null;
        $deliveryComment = 'NA';
        $clickCollect = null;
        $clickCollectType = null;
        $giftWrap = null;
        $flatNumber = null;
        $buildingNumber = null;
        $roadNumber = null;
        $block = null;
        $country = null; //$order->getNationality();
        $zipCode = null;
        $residingCountry = null; //$order->getNationality();
        $collectionPoint = null;        
        $collectionDate = null;
        $deliveryDate = null;
        $collectionTime = null;
        $flightNumber = null;
        $airline = null;        
        $homeDevlivery = null;
        $homeDevliveryAddress = null;
        $arrivalDate = null;
        $arrivalTime = null;
        $departureDate = null;
        $departureTime = null;
        $destination = null;
        $preOrder = 'No';
        $discountGiftVoucher = 'NA';
        $giftVoucherSeriesNumber = 'NA';
        $voucherType = 'NA';
        $voucherAmount = 'NA';
        $vatForExcerice = null;
        $vatForCustoms = $order->getBaseHandlingChargesTax();

        if( $order->getStoreId() == 2){ //arrival
            $collectionPoint = $order->getCollectionPoint();
            $collectionDate = $order->getCollectionDate();
            $flightNumber = $order->getFlightNumber();
            $airline = $order->getAirline();
            $arrivalDate = $order->getCollectionDate();
            $arrivalTime = $order->getFlightTime();
            $clickCollect = 'Yes';
            $clickCollectType = 'Arrival';
        }

        if( $order->getStoreId() == 3){ //departure
            $collectionPoint = $order->getCollectionPoint();
            $collectionDate = $order->getCollectionDate();
            $collectionTime = $order->getCollectionTime();
            $flightNumber = $order->getFlightNumber();
            $airline = $order->getAirline();
            $departureDate = $order->getJourneyDate();
            $departureTime = $order->getFlightTime();
            $destination = $order->getDestination();
            $clickCollect = 'Yes';
            $clickCollectType = 'Departure';
        }

        if( $order->getStoreId() == 4){ //home delivery
            $homeDevlivery = 'Yes';
            $clickCollect = 'No';
            $address = $this->getAddressData($order->getShippingAddressId())->getData();
            $zipCode = $address['postcode'];
            $homeDevliveryAddress = $address['street'];
            $street = explode(PHP_EOL,$address['street']);
            foreach ($street as $key => $streetVal) {
                switch ($key) {
                    case 0:
                        $flatNumber = $streetVal;
                        break;
                    case 1: 
                        $buildingNumber = $streetVal;
                        break;
                    case 2:
                        $roadNumber = $streetVal;
                        break;
                    case 3:
                        $block = $streetVal;
                        break;
                    default:
                        break;
                }
            }            
        }

        $pickupByAnotherPerson = 'No';
        if ($order->getPickupType() == 2) {
            $pickupByAnotherPerson = 'Yes';
        }        
        $pickupPersonName = $order->getAnotherPersonName();
        $pickupPersonMobileNumber = $order->getAnotherPersonPhone();

        $onHandTobbaco = 0;
        $onHandSpirit = 0;
        $onHandQty = 0;
        $flvTobbaco = 0;
        $onHandBeer = 0;
        if ($order->getQuantityOnHand()) {
            $qtyObj = json_decode($order->getQuantityOnHand(), TRUE);
            foreach($qtyObj as $key => $value) {
                if ($value) {
                    switch ($key) {
                        case 'FLV TOBACCO':
                            $onHandTobbaco = $value;
                            break;
                        case 'SPIRIT+WINE':
                            $onHandSpirit = $value;
                            break;
                        case 'TOBACCO':
                            $flvTobbaco = $value;
                            break;
                        case 'BEER':
                            $onHandBeer = $value;
                            break;
                        default:
                            break;
                    }                 
                }
            }
            $onHandQty = $onHandTobbaco+$onHandSpirit+$flvTobbaco+$onHandBeer;
        }

        $prodcutData = $this->getProductData($order);

        $xmlData = sprintf('<CALTestSuites xmlns="urn:microsoft-dynamics-nav/xmlports/x61013">
            <MagentoOrderHeader>
            <entry_id>%s</entry_id>
            <store_id>%s</store_id>
            <customer_id>%s</customer_id>
            <discount_amount>%s</discount_amount>
            <grand_total>%s</grand_total>
            <tax_amount>%s</tax_amount>
            <total_paid>%s</total_paid>
            <total_qty_ordered>%s</total_qty_ordered>
            <customer_is_guest>%s</customer_is_guest>
            <subtotal_incl_tax>%s</subtotal_incl_tax>
            <customer_dob>%s</customer_dob>
            <increment_id>%s</increment_id>
            <applied_rule_ids>%s</applied_rule_ids>
            <base_currency_code>%s</base_currency_code>
            <customer_email>%s</customer_email>
            <customer_firstname>%s</customer_firstname>
            <customer_lastname>%s</customer_lastname>
            <customer_middlename>%s</customer_middlename>
            <customer_prefix>%s</customer_prefix>
            <discount_description>%s</discount_description>
            <order_currency_code>%s</order_currency_code>
            <shipping_method>%s</shipping_method>
            <store_currency_code>%s</store_currency_code>
            <store_name>%s</store_name>
            <created_at>%s</created_at>
            <updated_at>%s</updated_at>
            <total_item_count>%s</total_item_count>
            <customer_gender>%s</customer_gender>
            <shipping_incl_tax>%s</shipping_incl_tax>
            <coupon_rule_name>%s</coupon_rule_name>
            <delivery_date>%s</delivery_date>
            <delivery_comment>%s</delivery_comment>            
            <Flat_No>%s</Flat_No>
            <Building_No>%s</Building_No>
            <Road_No>%s</Road_No>
            <Block>%s</Block>
            <Country>%s</Country>
            <ZipCode>%s</ZipCode>
            <Mobile_No>%s</Mobile_No>
            <Mobile_No>%s</Mobile_No>
            <Pickup_by_Another_person>%s</Pickup_by_Another_person>
            <Pickup_person_Name>%s</Pickup_person_Name>
            <Pickup_Person_Mobile_No.>%s</Pickup_Person_Mobile_No.>
            <PassportNo_Nationality_ID>%s</PassportNo_Nationality_ID>
            <Residing_Country>%s</Residing_Country>
            <Collection_Point>%s</Collection_Point>
            <Collection_Date>%s</Collection_Date>
            <Collection_Time>%s</Collection_Time>
            <Click_Collect>%s</Click_Collect>
            <Click_Collect_Type>%s</Click_Collect_Type>
            <Home_Delivery>%s</Home_Delivery>
            <Home_Delivery_Address>%s</Home_Delivery_Address>
            <Arrival_Date>%s</Arrival_Date>
            <Arrival_Time>%s</Arrival_Time>
            <Departure_Date>%s</Departure_Date>
            <Departure_Time>%s</Departure_Time>
            <Flight_No>%s</Flight_No>
            <Airline>%s</Airline>
            <Destination>%s</Destination>
            <PreOrder>%s</PreOrder>
            <Discount_Gift_Voucher>%s</Discount_Gift_Voucher>
            <Voucher_serial_No>%s</Voucher_serial_No>
            <Voucher_Type>%s</Voucher_Type>
            <Voucher_Amount>%s</Voucher_Amount>
            <VAT_for_Excise>%s</VAT_for_Excise>
            <VAT_for_customs>%s</VAT_for_customs>
            <On_Hand_Qty_TOBACCO>%s</On_Hand_Qty_TOBACCO>
            <On_Hand_Qty_SPIRIT_WINE>%s</On_Hand_Qty_SPIRIT_WINE>
            <On_Hand_Qty>%s</On_Hand_Qty>
            <FLV_TOBACCO>%s</FLV_TOBACCO>
            <On_Hand_Qty_BEER>%s</On_Hand_Qty_BEER>
            <Payment_ID>%s</Payment_ID>
            %s
            </MagentoOrderHeader>
            </CALTestSuites>',
            $order->getId(),
            $order->getStoreId(),
            $customerId,
            $order->getBaseDiscountAmount(),
            $order->getBaseGrandTotal(),
            $order->getBaseTaxAmount(),
            $order->getBaseSubTotal(),
            $order->getTotalQtyOrdered(),
            $order->getCustomerIsGuest(),
            $order->getBaseSubtotalInclTax(),
            $dob,
            $order->getIncrementId(),
            $order->getAppliedRuleIds(),
            $order->getBaseCurrencyCode(),
            $order->getCustomerEmail(),
            $order->getCustomerFirstname(),
            $order->getCustomerLastname(),
            $order->getCustomerMiddlename(),
            $customerPrefix,
            $order->getDiscountDescription(),
            $order->getOrderCurrencyCode(),
            $order->getShippingMethod(),
            $order->getStoreCurrencyCode(),
            $order->getStore()->getGroup()->getName(),
            $order->getCreatedAt(),
            $order->getUpdatedAt(),
            $order->getTotalItemCount(),
            $gender,
            $order->getBaseShippingInclTax(),
            $coupenRuleName,
            $deliveryDate,
            $deliveryComment,
            $flatNumber,
            $buildingNumber,
            $roadNumber,
            $block,
            $country,
            $zipCode,
            $mobileNumber,
            $countryCode,
            $pickupByAnotherPerson,
            $pickupPersonName,
            $pickupPersonMobileNumber,
            $order->getPassportNumber(),
            $residingCountry,
            $collectionPoint,
            $collectionDate,
            $collectionTime,
            $clickCollect,
            $clickCollectType,
            $homeDevlivery,
            $homeDevliveryAddress,
            $arrivalDate,
            $arrivalTime,
            $departureDate,
            $departureTime,
            $flightNumber,
            $airline,
            $destination,
            $preOrder,
            $discountGiftVoucher,
            $giftVoucherSeriesNumber,
            $voucherType,
            $voucherAmount,
            $vatForExcerice,
            $vatForCustoms,
            $onHandTobbaco,
            $onHandSpirit,
            $onHandQty,
            $flvTobbaco,
            $onHandBeer,
            $order->getPayment()->getEntityId(),
            $prodcutData
        );
        return $xmlData;
    }

    /**
     * Get Product Data
     *
     * @param object $order
     * @return string
     */
    public function getProductData($order)
    {        
        $productData = []; 
        foreach ($order->getItems() as $item) {
            $product = $this->getProductByData($item->getProductId());
            $lineNo = null;
            $paymentId = $order->getPayment()->getEntityId();
            $entityId = $product->getId();
            $createdAt = $product->getCreatedAt();
            $updatedAt = $product->getUpdatedAt();
            $qtyInvoice = null;
            $vatAmount = null;
            $excercieAmount = null;
            $customAmount = null;
            $vatExcrice = null;
            $vatCustoms = $item->getBaseHandlingChargesTax();
            $customDutyPerItem = null;
            $customConsiderdQty = null;
            
            $discountDescription = null;
            if($item->getAppliedRuleIds()){
                $ruleIds = explode(',', $item->getAppliedRuleIds());
                $discountDescription = '';
                foreach ($ruleIds as $key => $ruleId) {
                    $salesRule = $this->ruleRepository->getById($ruleId);
                    $discountDescription .= $salesRule->getDescription().', ';
                }
            }

            $raffleTicketSeriesNumber = null;
            $raffleTicketNumber = null;
            if ($product->getResource()->getAttribute('is_check_raffle')->getFrontend()->getValue($product) == 'Yes') {
                $raffleTicketSeriesNumber = $product->getResource()->getAttribute('series')->getFrontend()->getValue($product);;
                $raffleTicketNumber = ( $item->getProductOptions()['options'][0]['label'] == 'Raffle Ticket') ? $item->getProductOptions()['options'][0]['value']: '';
            }

            $parentCategory = null;
            $customCategory = $product->getCustomAllowenceCategory();
            if($customCategory) {
                $parentCategory = $this->getParentCategory($customCategory)->getData();
                if($parentCategory['Parent_Custom_Category']) {
                    $customCategory = str_replace('+', '_', $parentCategory['Parent_Custom_Category']);
                } else {
                    $customCategory = str_replace(' ', '_', $customCategory);
                }
            }
            
            $productData[] =  sprintf('<MagentoOrderLine>
                <entry_id>%s</entry_id>
                <line_no>%s</line_no>
                <store_id>%s</store_id>
                <created_at>%s</created_at>
                <updated_at>%s</updated_at>
                <product_id>%s</product_id>
                <product_type>%s</product_type>
                <sku>%s</sku>
                <name>%s</name>
                <description>%s</description>
                <applied_rule_ids>%s</applied_rule_ids>
                <no_discount>%s</no_discount>
                <qty_backordered>%s</qty_backordered>
                <qty_invoiced>%s</qty_invoiced>
                <gift_wrap>%s</gift_wrap>
                <price>%s</price>
                <base_currency_code>%s</base_currency_code>
                <tax_percent>%s</tax_percent>
                <tax_amount>%s</tax_amount>
                <discount_percent>%s</discount_percent>
                <discount_amount>%s</discount_amount>
                <customer_prefix>%s</customer_prefix>
                <row_total>%s</row_total>
                <tax_before_discount>%s</tax_before_discount>
                <price_incl_tax>%s</price_incl_tax>
                <row_total_incl_tax>%s</row_total_incl_tax>
                <free_shipping>%s</free_shipping>
                <vat_amount>%s</vat_amount>
                <excise_amount>%s</excise_amount>
                <custom_amount>%s</custom_amount>
                <unit_price>%s</unit_price>
                <VAT_for_Excise>%s</VAT_for_Excise>
                <VAT_for_Customs>%s</VAT_for_Customs>
                <Custom_Category_%s/>
                <Custom_Duty_Per_Item>%s</Custom_Duty_Per_Item>
                <Custom_Considered_Qty>%s</Custom_Considered_Qty>
                <Discount_Description>%s</Discount_Description>
                <Raffle_Ticket_Series_No>%s</Raffle_Ticket_Series_No>
                <Raffle_Ticket_No>%s</Raffle_Ticket_No>
                </MagentoOrderLine>',  
                $entityId,
                $lineNo,
                $item->getStoreId(),
                $createdAt,
                $updatedAt,
                $item->getProductId(),
                $item->getProductType(),
                $item->getSku(),
                $item->getName(),
                $item->getDescription(),
                $item->getAppliedRuleIds(),
                $item->getDiscountAmount(),
                $item->getQtyOrdered(),
                $qtyInvoice,
                $item->getGwId(),
                $item->getPrice(),
                $order->getBaseCurrencyCode(),
                $item->getTaxPercent(),
                $item->getTaxAmount(),
                $item->getDiscountPercent(),
                $item->getDiscountAmount(),
                $order->getCustomerPrefix(),
                $item->getRowTotal(),
                $item->getBaseTaxAmount(),
                $item->getPriceInclTax(),
                $item->getRowTotalInclTax(),
                $item->getFreeShipping(),
                $vatAmount,
                $excercieAmount,
                $customAmount,
                $item->getOriginalPrice(),
                $vatExcrice,
                $vatCustoms,
                $customCategory,
                $customDutyPerItem,
                $customConsiderdQty,
                $discountDescription,
                $raffleTicketSeriesNumber,
                $raffleTicketNumber
            );
        }
        
        if (!empty($productData)) {
            $productData = implode("", $productData);
        }
        return $productData;
    }

    /**
     * Get Product BY Id
     *
     * @param int $productId
     * @return object
     */
    public function getProductByData($productId)
    {
        try {
           $product = $this->productRepositoryInterface->getById($productId);
           return $product;
        } catch (NoSuchEntityException $e) {
           return null;
        }
    }

    /**
     * Get address details
     *
     * @return string|null
     */
    public function getAddressData($addressId)
    {
        $address = $this->addressCollection->create()->addFieldToFilter('entity_id',array($addressId))->getFirstItem();
        return $address;
    }

    /**
     * Get Parent_Custom_Category
     *
     * @return string|null
     */
    public function getParentCategory($code)
    {
        $parentCategory = $this->calculationCollection->create()->addFieldToFilter('Code', $code)->getFirstItem();
        return $parentCategory;
    }
}
