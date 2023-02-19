<?php

namespace Ecommage\CustomerUpdatePassword\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Ecommage\CustomerPersonalDetail\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Sparsh\MobileNumberLogin\Setup\InstallData;

/**
 *
 */
class Data extends AbstractHelper
{
    public function __construct
    (
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        Session $customerSession,
        Context $context
    )
    {
        $this->session = $customerSession;
        $this->dataPersistor =$dataPersistor;
        parent::__construct($context);
    }


    public function getToken()
    {
        return $this->dataPersistor->get('token_ecommage');
    }

    public function getStoredRpCustomerId()
    {
       return (int)$this->session->getRpCustomerId();
    }
}
