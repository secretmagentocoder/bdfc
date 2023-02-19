<?php
/**
 * 
 * @package Bdfc_General
 */
declare(strict_types=1);

namespace Bdfc\General\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Bdfc\General\Helper\ProductConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AdditionalDataConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ProductConfig
     */
    private $productConfigHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Additional Data config provider
     *
     * @param CheckoutSession $checkoutSession
     * @param ProductConfig $productConfigHelper
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ProductConfig $productConfigHelper,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productConfigHelper = $productConfigHelper;
        $this->scopeConfig = $scopeConfig;
    }
    
    public function getConfig()
    {
        $configArray = [];
        $currentQuote = $this->checkoutSession->getQuote();

        $categoryIds = [];
        foreach ($currentQuote->getAllVisibleItems() as $item) {
            $categoryIds = array_merge($categoryIds, $item->getproduct()->getCategoryIds());
        }
        $categoryIds = array_unique($categoryIds);

        $ageLimit = $this->productConfigHelper->getAgeLimit($categoryIds);
        $currentYear = date("Y");
        $yearLimit = null;
        $yearRange = '-1000:+0';

        if (isset($ageLimit['age_limit'])) {
            switch ($ageLimit['age_limit']) {
                case 18:
                    $yearLimit = $currentYear - 18;
                    $yearRange = '-1000:'.$yearLimit;
                    break;
                case 21:
                    $yearLimit = $currentYear - 21;
                    $yearRange = '-1000:'.$yearLimit;
                    break;
            }
        }

        $configArray['year_limit'] = $yearRange;
        return $configArray;
    }

    /**
     * Block Multiple Orders
     */
    public function isEnabledOrderRestriction()
    {
        return $this->scopeConfig->getValue(
            'checkout/order_restrictions/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }
    
    /**
     * Block Time
     */
    public function getOrderRestrictionHour()
    {
        return $this->scopeConfig->getValue(
            'checkout/order_restrictions/hours',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }
    
    /**
     * Allow Order before Flight Restriction
     */
    public function isEnabledAllowPlaceOrder()
    {
        return $this->scopeConfig->getValue(
            'checkout/order_allow_time/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }
    
    /**
     * Hours before Flight
     */
    public function getOrderAllowTime()
    {
        return $this->scopeConfig->getValue(
            'checkout/order_allow_time/hours',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }
}
