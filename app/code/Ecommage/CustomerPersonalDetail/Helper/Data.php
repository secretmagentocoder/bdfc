<?php

namespace Ecommage\CustomerPersonalDetail\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Ecommage\CustomerPersonalDetail\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Model\StoreManagerInterface;
use Safe\Exceptions\JsonException;
use Sparsh\MobileNumberLogin\Setup\InstallData;
use Magento\Framework\App\ResourceConnection;

/**
 *
 */
class Data extends AbstractHelper
{
    /**
     * @var TimezoneInterface
     */
    protected $_date;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var IsSubscribed
     */
    protected $is_subcribed;
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var CountryCollectionFactory
     */
    protected $countryCollection;

    /**
     * @var RegionCollectionFactory
     */
    protected $regionCollection;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $eavAttributeRepository;

    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @var Subscriber
     */
    private $subscriber;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Data constructor.
     *
     * @param RequestInterface             $request
     * @param Region                       $region
     * @param AddressFactory               $addressFactory
     * @param Context                      $context
     * @param CurrentCustomer              $currentCustomer
     * @param CountryCollectionFactory     $countryCollection
     * @param RegionCollectionFactory      $regionCollection
     * @param CustomerRepositoryInterface  $customerRepositoryInterface
     * @param Session                      $customerSession
     * @param AttributeRepositoryInterface $eavAttributeRepositoryInterface
     * @param Config                       $eavConfig
     * @param StoreManagerInterface        $storeManager
     * @param Subscriber                   $subscriber
     * @param CustomerRepositoryInterface  $customerRepository
     * @param TimezoneInterface            $date
     */
    public function __construct(
        RequestInterface $request,
        Region $region,
        AddressFactory $addressFactory,
        Context $context,
        CurrentCustomer $currentCustomer,
        CountryCollectionFactory $countryCollection,
        RegionCollectionFactory $regionCollection,
        CustomerRepositoryInterface $customerRepositoryInterface,
        Session $customerSession,
        AttributeRepositoryInterface $eavAttributeRepositoryInterface,
        Config $eavConfig,
        StoreManagerInterface $storeManager,
        Subscriber $subscriber,
        CustomerRepositoryInterface $customerRepository,
        TimezoneInterface $date,
        ResourceConnection $resourceConnection
    ) {
        $this->_date                       = $date;
        $this->customerRepository          = $customerRepository;
        $this->request                     = $request;
        $this->region                      = $region;
        $this->_addressFactory             = $addressFactory;
        $this->storeManager                = $storeManager;
        $this->_eavConfig                  = $eavConfig;
        $this->eavAttributeRepository      = $eavAttributeRepositoryInterface;
        $this->customerSession             = $customerSession;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->regionCollection            = $regionCollection;
        $this->countryCollection           = $countryCollection;
        $this->currentCustomer             = $currentCustomer;
        $this->subscriber                  = $subscriber;
        $this->resource                  = $resourceConnection;
        parent::__construct($context);
    }

    /**
     * @return string
     * @throws JsonException
     */
    public function getCountryCollectionJson()
    {
        $arr         = [];
        $countryData = $this->countryCollection->create();
        foreach ($countryData as $items) {
            $arr[$items->getCodeId()] = $items->getName();
        }
        return \Safe\json_encode($arr);
    }

    /**
     * @return string
     * @throws JsonException
     */
    public function getRegionCollectionJson()
    {
        $arr        = [];
        $regionData = $this->regionCollection->create();
        foreach ($regionData as $datum) {
            $arr[0][$datum->getCountryId()][$datum->getRegionId()] = $datum->getName();
        }

        return \Safe\json_encode($arr);
    }

    /**
     * @return string|null
     */
    public function getCountryCode()
    {
        $countryCodeAttribute = $this->currentCustomer->getCustomer()
                                                      ->getCustomAttribute(InstallData::COUNTRY_CODE);
        return $countryCodeAttribute ? (string)$countryCodeAttribute->getValue() : null;
    }

    /**
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomer()
    {
        $customer = $this->customerRepositoryInterface->getById($this->customerSession->getCustomer()->getId());
        return $customer;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        $customerMobileNumber = $this->getCustomer()->getCustomAttribute('mobile_number');
        return $customerMobileNumber ? $customerMobileNumber->getValue() : '';
    }

    /**
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getPassportNo()
    {
        return $this->getCustomer()->getCustomAttribute('passport_no')->getValue();
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws JsonException
     */
    public function getNational()
    {
        $id    = $this->getCustomer()->getCustomAttribute('national_id') ? $this->getCustomer()->getCustomAttribute('national_id')->getValue() : '';
        $array = [];
        if ($id) {
            $data                = $this->region->load($id);
            $array['optionData'] = ['id' => $data->getRegionId(), 'value' => $data->getCountryId()];
        }
        return \Safe\json_encode($array);
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws JsonException
     */
    public function getNationalCustomJsons()
    {
        $nationalAttr    = $this->_eavConfig->getAttribute('customer', 'nationality');
        $nationalOptions = $nationalAttr->getSource()->getAllOptions();
        $array           = [];
        if ($nationalOptions) {
            $array = ['options' => $nationalOptions];
        }
        return \Safe\json_encode($array);
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws JsonException
     */
    function isRegionOption()
    {
        $nationalId = $this->getCustomer()->getCustomAttribute('national_id') ? $this->getCustomer()->getCustomAttribute('national_id')->getValue() : '';

        if ($this->region->load($nationalId)->getId()) {
            return \Safe\json_encode(['is' => true, 'id' => $nationalId]);
        }
        return \Safe\json_encode(['is' => false, 'id' => $nationalId]);
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws JsonException
     */
    public function getNationalDefautlAddress()
    {
        $regionId            = $this->getAddress() ? $this->getAddress()->getRegionId() : null;
        $countryId           = $this->getAddress() ? $this->getAddress()->getCountryId() : null;
        $array['optionData'] = ['id' => $regionId, 'value' => $countryId];
        return \Safe\json_encode($array);
    }

    public function getNationalities(){
        $connection = $this->resource->getConnection();
        $select = $connection->select()
                    ->from(
                        'ecommage_nationalities',
                        ['*']
                    );
        $nationalities = $connection->fetchAll($select);
        return $nationalities;
    }

    public function getNationalCustomer()
    {
        $id = $this->getCustomer()->getCustomAttribute('national_id') ? $this->getCustomer()->getCustomAttribute('national_id')->getValue() : '';
        return $id;
    }


    /**
     * @return array
     */
    public function getPrefixOptions(): array
    {
        $prefixs = $this->scopeConfig->getValue('customer/address/prefix_options');
        if (empty($prefixs)) {
            return [];
        }

        if (strpos($prefixs, ';') !== false) {
            return explode(';', $prefixs);
        }

        return (array)$prefixs;
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isSubcribed()
    {
        $checkSubscriber = $this->subscriber->loadByCustomerId($this->customerSession->getCustomerId());

        if ($checkSubscriber->isSubscribed()) {
            return true;
        }
        return false;
    }

    /**
     * @return \Magento\Customer\Model\Address
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAddress()
    {
        return $this->_addressFactory->create()->load($this->getCustomer()->getDefaultShipping());
    }

    /**
     * @return bool
     */
    public function getActionPage()
    {
        $status = true;
        if ($this->request->getFullActionName() == 'customer_address_form') {
            $status = false;
        }
        return $status;
    }

    /**
     * @return array
     */
    public function getCountryCollection()
    {
        $arr         = [];
        $countryData = $this->countryCollection->create();
        foreach ($countryData as $items) {
            $arr[$items->getCodeId()] = $items->getName();
        }
        return $arr;
    }

    public function getCountryCollectionArray()
    {
        $countryData = $this->countryCollection->create()->getData();
       return $countryData;
    }

    /**
     * @return string|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isDefaultShippingAddress()
    {
        $customer = $this->customerRepository->getById($this->getCustomer()->getId());
        return $customer->getDefaultShipping() ? $customer->getDefaultShipping() : '';
    }

    /**
     * @return mixed|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getDobCustomer()
    {
        $dobFormat = null;
        if ($this->getCustomer()->getCustomAttribute('cust_dob')) {
            $dob       = $this->getCustomer()->getCustomAttribute('cust_dob')->getValue();
            $dobFormat = $this->_date->date($dob)->format('m/d/Y');
        }
        return $dobFormat;
    }
}
