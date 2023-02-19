<?php

namespace Amasty\Orderattr\Model\Checkout;

use Amasty\Orderattr\Model\ConfigProvider;
use Magento\Checkout\Model\ConfigProviderInterface;

class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $result = [];

        $result['amOrderAttribute']['sendOnShipping'] = $this->configProvider->isSendOnShipping();

        return $result;
    }
}
