<?php
namespace Sparsh\MobileNumberLogin\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;

/**
 * Class AuthenticateMobileNumber
 * @package Sparsh\MobileNumberLogin\Observer
 */
class AuthenticateMobileNumber implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Sparsh\MobileNumberLogin\Helper\Data
     */
    private $helperData;

    /**
     * AuthenticateMobileNumber constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param \Sparsh\MobileNumberLogin\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Sparsh\MobileNumberLogin\Helper\Data $helperData
    ) {
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $isModuleEnabled  = $this->helperData->isActive($storeId);

        if ($isModuleEnabled) {
            /** @var RequestInterface $request */
            $request = $observer->getEvent()->getRequest();
            $params = $request->getParams();
            $countryCode = isset($params['country_code']) ? $params['country_code'] : null;
            $this->registry->register('country_code', $countryCode);
        }
    }
}
