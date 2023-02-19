<?php

namespace Ecommage\CheckoutForm\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Eav\Model\Config;
use Ecommage\CheckoutForm\Helper\Data;
use Ecommage\RaffleTickets\Console\Command\UpdateCustomer;

class ConfigProviderPlugin
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @var RegionCollectionFactory
     */
    protected $regionCollection;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $quoteItem;

    /**
     * @var \Magento\Directory\Block\Data
     */
    protected $optionCountry;

    protected $helperAddress;

    protected $countryCollectionFactory;

    protected $helperPersonal;

    /**
     * @param CustomerRepository $customerRepository
     * @param CustomerSession $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Catalog\Model\Product $product,
        Data $helper,
        Config                                             $eavConfig,
        CustomerRepository                                 $customerRepository,
        CustomerSession                                    $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        RegionCollectionFactory                            $regionCollection,
        \Magento\Directory\Block\Data $optionCountry,
                \Magento\Customer\Helper\Address $helperAddress,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Ecommage\CustomerPersonalDetail\Helper\Data $helperPersonal

    )
    {
        $this->helperPersonal = $helperPersonal;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->helperAddress = $helperAddress;
        $this->optionCountry = $optionCountry;
        $this->serializer = $serializer;
        $this->product = $product;
        $this->quoteItem = $quoteItem;
        $this->_storeManager = $storeManager;
        $this->_addressFactory = $addressFactory;
        $this->helper = $helper;
        $this->regionCollection = $regionCollection;
        $this->_eavConfig = $eavConfig;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {
        if($this->customerSession->isLoggedIn()){
          $dobAttribute = $this->getCustomer()->getCustomAttribute('cust_dob') ? $this->getCustomer()->getCustomAttribute('cust_dob')->getValue() : '';
        if ($dobAttribute) {
            $dateModel = $this->dateTimeFactory->create();
            $result['dobFormat'] = $dateModel->gmtDate("d/m/Y", $dobAttribute);
        }

        // $result['nationalityCustom'] = $this->getNationalityCustom();
        $result['regionDefault'] = $this->getRegionCollection();
        $result['streetData'] = $this->getStreets();

        $result['baseUrl'] = $this->_storeManager->getStore()->getBaseUrl();
        }
        $check = [];
        $arr = [];
        $items = $result['totalsData']['items'];

        foreach ($items as $key => $item)
        {
            $items[$key]['display'] = 0;
            $quote = $this->quoteItem->load($item['item_id']);
            $product = $this->product->load($quote->getProductId());
            $check[] = $quote->getProductId();
            $items[$key]['is_check_raffle'] = 1;
            if (!empty($product->getIsCheckRaffle()) && $product->getTypeId() == 'virtual')
            {
                $items[$key]['is_check_raffle'] = 2;
                $items[$key]['booked_ticket'] = 'Booked Ticket No.';
            }
            if (in_array($quote->getProductId(),$check) && !empty($product->getIsCheckRaffle())){
               $options = $this->serializer->unserialize($item['options']);
               if(count($options)>0){
                 $count = explode(',',$options[0]['value']);
                $items[$key]['count'] = count($count);
               }
            }
        }

        $result['addressDefaultCustom'] = $this->getAddressDefault();
        $result['getOptionDeliverAt'] = $this->_eavConfig->getAttribute('customer_address', 'deliver_at')->getSource()->getAllOptions();
        $result['getOptionNationality'] = $this->helperPersonal->getNationalities();
        //$this->_eavConfig->getAttribute('customer', 'nationality')->getSource()->getAllOptions();
        if($this->customerSession->isLoggedIn()){
        $nationAttribute = $this->customerRepository->getById($this->customerSession->getCustomerId())->getCustomAttribute('national_id');
         $result['getNationalityId'] = !empty($nationAttribute) ? $nationAttribute->getValue() : '';
         }
         $result['streetLines'] = $this->getStreetLine();

       
        $result['regionDefault'] = $this->getRegionCollection();
        $result['countryCollectionData'] = $this->getCountryCollection();
        $result['totalsData']['items'] = $items;

        return $result;
    }


    /**
     * @return array
     */
    public function getAddressDefault(){
        $data = [
            'firstname'=>UpdateCustomer::FIRST_NAME_DEFAULT,
            'lastname'=>UpdateCustomer::LAST_NAME_DEFAULT,
            'dateofbirth'=>UpdateCustomer::DOB_DEFAULT,
            'mobile_number'=>UpdateCustomer::TELEPHONE_DEFAULT,
            'country_id'=>UpdateCustomer::COUNTRY_ID_DEFAULT,
            'region_id'=>UpdateCustomer::REGION_ID_DEFAULT,
            'city'=>UpdateCustomer::CITY_DEFAULT,
            'postcode'=>UpdateCustomer::POSTCODE_DEFAULT,
            'street'=>UpdateCustomer::STREET_DEFAULT,
            'telephone'=>UpdateCustomer::TELEPHONE_DEFAULT,

        ];
        $currentStore = $this->_storeManager->getStore();    
        if (in_array($currentStore->getCode(), ['arrival', 'departure'])) {
            $data['street'] = ['Pickup from '.$currentStore->getName().' Store'];
        }
        $storeId =$this->_storeManager->getStore()->getId();
        if($this->customerSession->isLoggedIn() && $storeId == 4 ){
            $value = $this->getCustomerShipping($this->customerSession->getCustomerData()->getAddresses(),$this->customerSession->getCustomerData()->getDefaultBilling());
            if (!empty($value)) {
                $data = [
                    'firstname'=> $value->getFirstname(),
                    'lastname'=>  $value->getLastname(),
                    'dateofbirth'=> $this->customerSession->getCustomerData()->getCustomAttributes()['mobile_number']->getValue(),
                    'mobile_number'=> $value->getTelephone(),
                    'country_id'=> $value->getCountryId(),
                    'region_id'=> UpdateCustomer::REGION_ID_DEFAULT,
                    'city'=> $value->getCity(),
                    'postcode'=> $value->getPostcode(),
                    'street'=> implode(',',$value->getStreet()),
                    'telephone'=>  $value->getTelephone(),
                ];
            }
        }
        return $data;
    }

    /**
     * @param $items
     *
     * @return array
     */
    public function setOption($items)
    {
        $arr = [];
        foreach ($items as $key => $option)
        {
            $quote = $this->quoteItem->load($option['item_id']);
            $product = $this->product->load($quote->getProductId());
            if (!empty($product->getIsCheckRaffle()) && $product->getTypeId() == 'virtual'){
                array_push($arr,$key);
            }
        }

        return $arr;
    }

    protected  function  getCustomerShipping($arr,$id)
    {
        $address = [];
        if (!empty($arr))
        {

            foreach ($arr as $item)
            {
                if ($item->getId() == $id)
                {

                    $address = $item;
                }
            }
        }
        return $address;
    }

    /**
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomer(): CustomerInterface
    {
        return $this->customerRepository->getById($this->customerSession->getCustomerId());
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getNationalityCustom()
    {
        $nationalAttr = $this->_eavConfig->getAttribute('customer', 'nationality');
        $nationalOptions = $nationalAttr->getSource()->getAllOptions();
        return $nationalOptions;
    }

    /**
     * @return mixed
     */
    public function getRegionCollection()
    {
        $arr = [];
        $regionData = $this->regionCollection->create()->getData();
        return $regionData;
    }

    /**
     * @return array
     */
    public function getStreets(){
        $shippingAddressId = $this->getCustomer()->getDefaultShipping();
        $shippingAddress = $this->_addressFactory->create()->load($shippingAddressId);
        $streetAll = $shippingAddress->getStreet();
        $street = [];
        $streetLine  = $this->helper->getStreetLines();

        for ($_i = 1, $_n = $streetLine; $_i <= $_n; $_i++){
                if(isset($streetAll[$_i - 1])){
                    array_push($street,$streetAll[$_i - 1]);
                }else{
                    array_push($street,'');
                }
        }
        return $street;
    }

    public function getStreetLine(){
        $data = $this->helperAddress->getStreetLines();
        $streetArr = [];

        for ($_i = 1; $_i <= $data; $_i++){
            array_push($streetArr,'');
        }

        return $streetArr;
    }

    public function getCountryCollection(){
        $data = $this->helperPersonal->getCountryCollectionArray();
        return $data;
    }
}
