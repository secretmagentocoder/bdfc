<?php
namespace Sparsh\MobileNumberLogin\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AuthenticateAjaxMobileNumber
 * @package Sparsh\MobileNumberLogin\Observer
 */
class AuthenticateAjaxMobileNumber implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

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
     * AuthenticateAjaxMobileNumber constructor.
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param \Sparsh\MobileNumberLogin\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Sparsh\MobileNumberLogin\Helper\Data $helperData
    ) {
        $this->jsonDecoder = $jsonDecoder;
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
        $isModuleEnabled = $this->helperData->isActive($storeId);
        if ($isModuleEnabled) {
            /** @var RequestInterface $request */
            $request = $observer->getEvent()->getRequest();
            $credentials = $this->jsonDecoder->decode($request->getContent());
            $countryCode = isset($credentials['country_code']) ? $credentials['country_code'] : null;
            $this->registry->register('country_code', $countryCode);
        }
    }
}
