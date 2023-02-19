<?php declare(strict_types=1);
/**
 * @package Ceymox_Navconnector
 */
namespace Ceymox\Navconnector\Model\CustomDuty;

use Ceymox\Navconnector\Model\Curl\CurlOperations;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;


class CustomDutyManager
{

    /**
     * @var CurlOperations
     */
    private $curlOperations;

    /**
     * @var Config
     */
    private $resourceConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;



    /**
     * Construction function
     *
     * @param CurlOperations $curlOperations
     * @param Config $resourceConfig
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CurlOperations $curlOperations,
        Config $resourceConfig,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
        
    ) {
        $this->curlOperations = $curlOperations;
        $this->resourceConfig = $resourceConfig;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Execution get Custom Duty
     *
     */
    public function getCustomDuty()
    {
        $customDuty = $this->curlOperations->makeRequest('WebCompanyInformation');
        if (!empty($customDuty)) {
            if (isset($customDuty['value'])) {
                $vatCustomDuty = isset($customDuty['value']['0']['VATPercent_for_Custom_Duty']) ? $customDuty['value']['0']['VATPercent_for_Custom_Duty']: 0;
                $this->setCustomDutyValue($vatCustomDuty);
            }
        }
    }

    /**
     * Execution Set Custom Duty value
     * 
     * @param $vatCustomDuty
     */
    public function setCustomDutyValue($vatCustomDuty)
    {

        $path = 'nav/system/vat_percentage_for_custom_duty';
        $value = $vatCustomDuty;
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES; 
        $this->storeManager->getStore()->getWebsiteId();
        $websiteCode = 'arrival_website';
        $websiteId = $this->storeManager->getWebsite($websiteCode)->getId();
        $this->resourceConfig->saveConfig($path, $value, $scope, $websiteId);
        print_r("Custom Duty Saved");
    }

    /**
     * Get getCustomDutyValue
     *
     * @return void
     */
    public function getCustomDutyValue()
    {
        $value =  $this->scopeConfig->getValue(
            'nav/system/vat_percentage_for_custom_duty',
            ScopeInterface::SCOPE_WEBSITES
        );
        return $value;
    }
}
