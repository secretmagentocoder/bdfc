<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ecommage\CustomerPersonalDetail\Block\Address;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Helper\Address;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Api\Data\RegionInterfaceFactory;

/**
 * Customer address edit block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Edit extends \Magento\Directory\Block\Data
{
    /**
     * @var \Magento\Customer\Api\Data\AddressInterface|null
     */
    protected $_address = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadata;

    protected $helperData;

    protected $regionDataFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param array $data
     * @param AddressMetadataInterface|null $addressMetadata
     * @param Address|null $addressHelper
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Ecommage\CustomerPersonalDetail\Helper\Data $helperData,
        RegionInterfaceFactory $regionDataFactory,
        array $data = [],
        AddressMetadataInterface $addressMetadata = null,
        Address $addressHelper = null
    ) {
        $this->regionDataFactory = $regionDataFactory;
        $this->helperData = $helperData;
        $this->_customerSession = $customerSession;
        $this->_addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->currentCustomer = $currentCustomer;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->addressMetadata = $addressMetadata ?: ObjectManager::getInstance()->get(AddressMetadataInterface::class);
        $data['addressHelper'] = $addressHelper ?: ObjectManager::getInstance()->get(Address::class);
        $data['directoryHelper'] = $directoryHelper;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
    }

    /**
     * Prepare the layout of the address edit block.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->initAddressObject();

        $this->pageConfig->getTitle()->set($this->getTitle());

        if ($postedData = $this->_customerSession->getAddressFormData(true)) {
            $postedData['region'] = [
                'region_id' => isset($postedData['region_id']) ? $postedData['region_id'] : null,
                'region' => $postedData['region'],
            ];
            $this->dataObjectHelper->populateWithArray(
                $this->_address,
                $postedData,
                \Magento\Customer\Api\Data\AddressInterface::class
            );
        }
        $this->precheckRequiredAttributes();
        return $this;
    }

    /**
     * Initialize address object.
     *
     * @return void
     */
    private function initAddressObject()
    {
        // Init address object
        $addressDefault = $this->helperData->getAddress();
        if($addressDefault){
            $this->_address = $this->addressDataFactory->create();
           $this->_address->setStreet($addressDefault->getStreet())
           ->setCountryId($addressDefault->getCountryId())
           ->setCity($addressDefault->getCity())
           ;

            if(isset($addressDefault['region_id'])){
                $this->_address->setRegionId($addressDefault['region_id']);
            }
            if(isset($addressDefault['region'])){
                $regionFactory = $this->regionDataFactory->create();
                $region = $regionFactory->setRegion($addressDefault['region']);
                $this->_address->setRegion($region);
            }

        }

        if ($this->_address === null) {
            $this->_address = $this->addressDataFactory->create();
            $customer = $this->getCustomer();
            $this->_address->setPrefix($customer->getPrefix());
            $this->_address->setFirstname($customer->getFirstname());
            $this->_address->setMiddlename($customer->getMiddlename());
            $this->_address->setLastname($customer->getLastname());
            $this->_address->setSuffix($customer->getSuffix());
        }
    }

    /**
     * Precheck attributes that may be required in attribute configuration.
     *
     * @return void
     */
    private function precheckRequiredAttributes()
    {
        $precheckAttributes = $this->getData('check_attributes_on_render');
        $requiredAttributesPrechecked = [];
        if (!empty($precheckAttributes) && is_array($precheckAttributes)) {
            foreach ($precheckAttributes as $attributeCode) {
                $attributeMetadata = $this->addressMetadata->getAttributeMetadata($attributeCode);
                if ($attributeMetadata && $attributeMetadata->isRequired()) {
                    $requiredAttributesPrechecked[$attributeCode] = $attributeCode;
                }
            }
        }
        $this->setData('required_attributes_prechecked', $requiredAttributesPrechecked);
    }

    /**
     * Generate name block html.
     *
     * @return string
     */
    public function getNameBlockHtml()
    {
        $nameBlock = $this->getLayout()
            ->createBlock(\Magento\Customer\Block\Widget\Name::class)
            ->setObject($this->getAddress());

        return $nameBlock->toHtml();
    }

    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'ecommage_customer_update/address/update'
        );
    }

    /**
     * Return the associated address.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * Return the specified numbered street line.
     *
     * @param int $lineNumber
     * @return string
     */
    public function getStreetLine($lineNumber)
    {
        $street = $this->_address->getStreet();
        return isset($street[$lineNumber - 1]) ? $street[$lineNumber - 1] : '';
    }

    /**
     * Return the country Id.
     *
     * @return int|null|string
     */
    public function getCountryId()
    {

        if ($countryId = $this->getAddress()->getCountryId()) {
            return $countryId;
        }
        return parent::getCountryId();
    }

    /**
     * Return the name of the region for the address being edited.
     *
     * @return string region name
     */
    public function getRegion()
    {
        $region = $this->getAddress()->getRegion();
        return $region === null ? '' : $region->getRegion();
    }

    /**
     * Return the id of the region being edited.
     *
     * @return int region id
     */
    public function getRegionId()
    {
        $region = $this->getAddress()->getRegion();
        return $region === null ? 0 : $region->getRegionId();
    }

    /**
     * Retrieve the number of addresses associated with the customer given a customer Id.
     *
     * @return int
     */
    public function getCustomerAddressCount()
    {
        return count($this->getCustomer()->getAddresses());
    }

    /**
     * Is the address the default shipping address?
     *
     * @return bool
     */
    public function isDefaultShipping()
    {
        return (bool)$this->getAddress()->isDefaultShipping();
    }

    /**
     * Retrieve the Customer Data using the customer Id from the customer session.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        return $this->currentCustomer->getCustomer();
    }


    /**
     * Get config value.
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
