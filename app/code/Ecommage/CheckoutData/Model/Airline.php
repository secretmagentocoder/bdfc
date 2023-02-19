<?php

namespace Ecommage\CheckoutData\Model;

class Airline extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'ecommage_checkout_airline';

    protected $_cacheTag = 'ecommage_checkout_airline';

    protected $_eventPrefix = 'ecommage_checkout_airline';

    protected function _construct()
    {
        $this->_init('Ecommage\CheckoutData\Model\ResourceModel\Airline');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
