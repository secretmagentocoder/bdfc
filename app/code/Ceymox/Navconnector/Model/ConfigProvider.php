<?php declare(strict_types=1);
/**
 * @package Ceymox_Navconnector
 */

namespace Ceymox\Navconnector\Model;

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

    /**
     * Get Company
     *
     * @return void
     */
    public function getCompany()
    {
        return $this->scopeConfig->getValue(
            'nav/system/company',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get Host
     *
     * @return void
     */
    public function getHost()
    {
        return $this->scopeConfig->getValue(
            'nav/system/host',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get User
     *
     * @return void
     */
    public function getUser()
    {
        return $this->scopeConfig->getValue(
            'nav/system/user',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get Password
     *
     * @return void
     */
    public function getPassword()
    {
        return $this->scopeConfig->getValue(
            'nav/system/pwd',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get Web Offer Header
     *
     * @return void
     */
    public function getWebOfferHeader()
    {
        return $this->scopeConfig->getValue(
            'nav/system/offer_header',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get Delivery Date
     *
     * @return void
     */
    public function getDeliveryDateApi()
    {
        return $this->scopeConfig->getValue(
            'nav/system/api_delivery_date',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get BaseUrl
     *
     * @return void
     */
    public function getBaseUrl()
    {
        return $this->storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }

    /**
     * Get Magento Username
     *
     * @return void
     */
    public function getMagentoUserName()
    {
        return $this->scopeConfig->getValue(
            'nav/magento_credentials/magento_user',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get Magento Password
     *
     * @return void
     */
    public function getMagentoPassword()
    {
        return $this->scopeConfig->getValue(
            'nav/magento_credentials/magento_pswd',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Enable order sync
     */
    public function isEnableOrderSync()
    {
        return $this->scopeConfig->getValue(
            'nav/nav_sync/enable_order_sync',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Enable credit memo sync
     */
    public function isEnableCreditMemoSync()
    {
        return $this->scopeConfig->getValue(
            'nav/nav_sync/enable_credit_memo_sync',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * check if status update api is enabled
     */
    public function isEnableStatusUpdateCron()
    {
        return $this->scopeConfig->getValue(
            'nav/nav_sync/enable_status_update_cron',
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
