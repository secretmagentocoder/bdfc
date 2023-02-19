<?php

namespace Ecommage\CustomerCheckoutSuccess\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const VAT_NUMBER_PATH = 'general/store_information/merchant_vat_number';

    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory
     */
    protected $orderFactory;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;
    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;


 /**
     * @param SerializerInterface                                                $serializer
     * @param \Magento\Directory\Model\CountryFactory                            $countryFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $orderFactory
     * @param \Magento\Catalog\Model\ProductRepository                           $productRepository
     * @param \Magento\Customer\Model\CustomerFactory                            $customerFactory
     * @param \Magento\Customer\Model\AddressFactory                             $addressFactory
     * @param Context                                                            $context
     */

      public function __construct
      (
          \Bodak\CheckoutCustomForm\Helper\Data $helper,
         \Magento\Customer\Model\Session $customerSession,
        SerializerInterface $serializer,
          \Magento\Directory\Model\CountryFactory $countryFactory,
          \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $orderFactory,
          \Magento\Catalog\Model\ProductRepository $productRepository,
          \Magento\Customer\Model\CustomerFactory $customerFactory,
          \Magento\Customer\Model\AddressFactory $addressFactory,
          Context $context,
          ScopeConfigInterface $scopeConfig
      )
      {
          $this->helper  = $helper;
         $this->_customerSession = $customerSession;
         $this->serializer = $serializer;
          $this->_countryFactory = $countryFactory;
          $this->orderFactory = $orderFactory;
          $this->_productRepository = $productRepository;
          $this->_customerFactory = $customerFactory;
          $this->_addressFactory = $addressFactory;
          $this->scopeConfig = $scopeConfig;
          parent::__construct($context);
      }

      public function getAddressCheckout($customerId)
      {
            if ($customerId)
            {
                 $customer =  $this->_customerFactory->create($customerId);
            }
      }

      public function isCheckTicket($order)
      {
          if ($order)
          {
              foreach ($order as $item)
              {
                  $product = $this->getProduct($item->getProductId());
                  if ($product->getData('is_check_raffle') == 1){
                      return true;
                  }
              }
          }
          return  false;
      }
      
      public function getProduct($id)
      {
          $product = [];
          if ($id){
              $product = $this->_productRepository->getById($id);
          }
          return $product;
      }

      public function getTitleTicket($options)
      {
         $value = [];
            if (array_key_exists('options',$options))
            {
                foreach ($options['options'] as $item)
                {
                    if ($item['option_type'] == 'checkbox'){
                        $value[] = $item['value'];
                    }
                }
                $value = implode(',',$value);
            }

           return $value;
      }


       public function isLogin()
      {
        if ($this->_customerSession->isLoggedIn())
        {
            return true;
        }
        return  false;
      }


      public function getInformation($order)
      {
          $information = [];
            if ($order)
            {
                 $information = $this->orderFactory->create()->addFieldToFilter('parent_id',$order->getOrderId())->getFirstItem();
            }
            
            return $information;
      }

    public function getCountryName($countryCode)
    {
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }


      public function covertDataJsonToArray($json)
    {
        $arr = [];
        if ($json)
        {
            $additionalData = $this->serializer->unserialize($json);
            foreach ($additionalData as $key => $item) {
                $value = $this->helper->getCustomCategoryCalculation($key)->getData();
                if (is_array($value) && count($value) > 0)
                {
                    $arr[] = [
                        'value' => $item,
                        'name' => $key,
                        'size' => $value[0]['Limit_UOM']
                    ];
                }

            }
        }

        return $arr;
    }


   public function countOptionRaffle($items)
      {
          if ($items)
          {
              $count = 0;
              foreach ($items as $item)
              {
                  if ($item->getProductOptions() && array_key_exists('options',$item->getProductOptions()) )
                  {
                    $order = $item->getProductOptions();
                      $count += $this->getOptionOrder($order['options']);
                  }
              }
          }
          return $count;

      }


      public function getOptionOrder($option)
      {
         if (!empty($option))
         {
             $count = 0;
             foreach ($option as $value)
             {
                 if (array_key_exists('value',$value)){
                   $arr =  count(explode(',',$value['value']));
                   $count += $arr;
                 }
             }
         }

         return $count;
      }

       /**
     * Get VAT number by website scope
     *
     * @return string|null
     */
    public function getVatNumberByWebsite()
    {
        return $this->scopeConfig->getValue(
            self::VAT_NUMBER_PATH,
            ScopeInterface::SCOPE_WEBSITES
        );
      }
}
