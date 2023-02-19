<?php

namespace Ecommage\CheckoutForm\Helper;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;

class Data extends AbstractHelper
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var Session
     */
    protected $customerSession;

    protected $addressRepository;

    protected $_eavConfig;

    protected $_request;

    protected $_storeManager;

    protected $_streetLines = [];

    protected $_addressMetadataService;

    protected $_address = null;

    /**
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param Session $customerSession
     * @param \Magento\Framework\App\Request\Http $request
     * @param Config $eavConfig
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(Context $context,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                AddressMetadataInterface $addressMetadataService,
                                CustomerRepositoryInterface $customerRepository,
                                Session $customerSession,
                                \Magento\Framework\App\Request\Http $request,
                                Config                          $eavConfig,
                                AddressRepositoryInterface $addressRepository)
    {
        $this->_storeManager = $storeManager;
        $this->_addressMetadataService = $addressMetadataService;
        $this->_request = $request;
        $this->_eavConfig = $eavConfig;
        $this->addressRepository = $addressRepository;
        $this->customerSession = $customerSession;
        $this->customerRepositoryInterface = $customerRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomer()
    {
        $customer = $this->customerRepositoryInterface->getById($this->customerSession->getCustomer()->getId());
        return $customer;
    }

    public function getStreetLines($store = null)
    {
        $websiteId = $this->_storeManager->getStore($store)->getWebsiteId();
        if (!isset($this->_streetLines[$websiteId])) {
            $attribute = $this->_addressMetadataService->getAttributeMetadata('street');

            $lines = $attribute->getMultilineCount();
            if ($lines <= 0) {
                $lines = 2;
            }
            $this->_streetLines[$websiteId] = min($lines, 20);
        }

        return $this->_streetLines[$websiteId];
    }

    public function getStreetLine($lineNumber)
    {
        $street = $this->_address->getStreet();
        return isset($street[$lineNumber - 1]) ? $street[$lineNumber - 1] : '';
    }
}
