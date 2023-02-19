<?php
namespace Sparsh\MobileNumberLogin\Helper;

/**
 * Class Data
 * @package Sparsh\MobileNumberLogin\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Get config value
     *
     * @param $config
     * @param $scopeCode = null
     * @return string
     */
    public function getConfig($config, $scopeCode = null)
    {
        return $this->scopeConfig->getValue($config, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    /**
     * Is module active
     *
     * @param $scopeCode
     * @return bool
     */
    public function isActive($scopeCode = null)
    {
        return (bool)$this->getConfig('sparsh_mobile_number_login/general/enable', $scopeCode);
    }

    /**
     * Retrieve login mode.
     *
     * @param $scopeCode
     * @return string
     */
    public function getLoginMode($scopeCode = null)
    {
        return (bool)$this->getConfig('sparsh_mobile_number_login/general/login_mode', $scopeCode);
    }
}
