<?php

namespace Ecommage\CustomerPersonalDetail\Helper;

use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;

class Address extends AbstractHelper
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

    public function __construct(Context $context,
    CustomerRepositoryInterface $customerRepository,
    Session $customerSession,
    Config                          $eavConfig,
    \Magento\Framework\App\Request\Http $request,
    AddressRepositoryInterface $addressRepository)
    {
        $this->_request = $request;
        $this->_eavConfig = $eavConfig;
        $this->addressRepository = $addressRepository;
        $this->customerSession = $customerSession;
        $this->customerRepositoryInterface = $customerRepository;
        parent::__construct($context);
    }

    public function getCustomer()
    {
        $customer = $this->customerRepositoryInterface->getById($this->customerSession->getCustomer()->getId());
        return $customer;
    }

    public function getDeliveryAt(){
        $addressAttribute = $this->_eavConfig->getAttribute('customer_address','deliver_at');
        $options = $addressAttribute->getSource()->getAllOptions();
        return $options;
    }

    public function getSaveUrl(){
        return $this->_urlBuilder->getUrl(
            'ecommage_customer_update/address/save',
            ['_secure' => true]
        );
    }

    public function getFirstname(){
        return $this->getCustomer()->getFirstname();
    }

    public function getLastName(){
        return $this->getCustomer()->getLastname();
    }
    
     /**
     * @return mixed|string
     */
    public function getParamIdUrl(){
        return $this->_request->getParam('id') ? $this->_request->getParam('id') : '';
    }
    
     public function getDeliveryId(){
        $addressId = $this->getParamIdUrl();
        if($addressId){
            // get address theo id
            $address = $this->addressRepository->getById($addressId);
            return $address->getCustomAttribute('deliver_at') ? $address->getCustomAttribute('deliver_at')->getValue() : null;
        }
        return null;
    }
}
