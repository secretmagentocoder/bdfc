<?php
namespace Sparsh\MobileNumberLogin\Block\Account\Dashboard;

use Sparsh\MobileNumberLogin\Setup\InstallData;

/**
 * Class Info
 * @package Sparsh\MobileNumberLogin\Block\Account\Dashboard
 */
class Info extends \Magento\Customer\Block\Account\Dashboard\Info
{
    /**
     * Get customer custom mobile number attribute as value.
     *
     * @return string Customer phone number value.
     */
    public function getMobileNumber()
    {
        $mobileNumberAttribute = $this->getCustomer()
            ->getCustomAttribute(InstallData::MOBILE_NUMBER);
        return $mobileNumberAttribute ? (string) $mobileNumberAttribute->getValue() : null;
    }

    /**
     * Get customer custom country code attribute as value.
     *
     * @return string Customer phone number value.
     */
    public function getCountryCode()
    {
        $countryCodeAttribute = $this->getCustomer()
            ->getCustomAttribute(InstallData::COUNTRY_CODE);
        return $countryCodeAttribute ? (string) $countryCodeAttribute->getValue() : null;
    }
}
