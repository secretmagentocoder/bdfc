<?php

namespace Ecommage\NavSyncPromotion\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigProvider
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * Constructors.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function getCompany()
    {
        return $this->scopeConfig->getValue(
            'nav/system/company',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getHost()
    {
        return $this->scopeConfig->getValue(
            'nav/system/host',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getUser()
    {
        return $this->scopeConfig->getValue(
            'nav/system/user',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getPassword()
    {
        return $this->scopeConfig->getValue(
            'nav/system/pwd',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getWebOfferHeader()
    {
        return $this->scopeConfig->getValue(
            'nav/system/offer_header',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getDeliveryDateApi()
    {
        return $this->scopeConfig->getValue(
            'nav/system/api_delivery_date',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getBaseUrl()
    {
        return $this->storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }

    public function getMagentoUserName()
    {
        return $this->scopeConfig->getValue(
            'nav/magento_credentials/magento_user',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getMagentoPassword()
    {
        return $this->scopeConfig->getValue(
            'nav/magento_credentials/magento_pswd',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * get credit memo prefix
     */
    public function getCreditMemoPrefix()
    {
        return $this->scopeConfig->getValue(
            'nav/nav_sync/creditmemo_prefix',
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
