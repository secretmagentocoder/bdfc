<?php
namespace Sparsh\MobileNumberLogin\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class MobileNumberConfigProvider
 * @package Sparsh\MobileNumberLogin\Model
 */
class MobileNumberConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Sparsh\MobileNumberLogin\Helper\Data
     */
    private $helperData;

    /**
     * MobileNumberConfigProvider constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Sparsh\MobileNumberLogin\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Sparsh\MobileNumberLogin\Helper\Data $helperData
    ) {
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $isModuleEnabled  = $this->helperData->isActive($storeId);
        $loginMode = $this->helperData->getLoginMode($storeId);

        return [
            'mobileNumberConfig' => [
                'moduleStatus' => $isModuleEnabled,
                'loginMode' => $loginMode,
            ],
        ];
    }
}
