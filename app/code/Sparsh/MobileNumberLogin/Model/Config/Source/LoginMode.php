<?php
namespace Sparsh\MobileNumberLogin\Model\Config\Source;

/**
 * Class LoginMode
 * @package Sparsh\MobileNumberLogin\Model\Config\Source
 */
class LoginMode
{
    const TYPE_BOTH = 1;

    const TYPE_MOBILE = 0;

    /**
     * Retrieve possible login options.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::TYPE_BOTH => __('Login with either email or mobile'),
            self::TYPE_MOBILE => __('Login with mobile only'),
        ];
    }
}
