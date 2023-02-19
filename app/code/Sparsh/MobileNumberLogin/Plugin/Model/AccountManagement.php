<?php
namespace Sparsh\MobileNumberLogin\Plugin\Model;

use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Sparsh\MobileNumberLogin\Setup\InstallData;

/**
 * Class AccountManagement
 * @package Sparsh\MobileNumberLogin\Plugin\Model
 */
class AccountManagement
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Sparsh\MobileNumberLogin\Helper\Data
     */
    private $helperData;

    /**
     * AccountManagement constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Sparsh\MobileNumberLogin\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Sparsh\MobileNumberLogin\Helper\Data $helperData
    ) {
        $this->storeManager = $storeManager;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->registry = $registry;
        $this->helperData = $helperData;
    }

    /**
     * Authenticate a customer by username and password
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeAuthenticate(\Magento\Customer\Model\AccountManagement $subject, $username, $password)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $isModuleEnabled  = $this->helperData->isActive($storeId);
        if ($isModuleEnabled) {
            $countryCode =  $this->registry->registry('country_code');
            if (is_numeric($username) && $countryCode) {
                $customer = $this->customerCollectionFactory->create();
                $collection = $customer->addAttributeToSelect('*')
                    ->addAttributeToFilter(InstallData::MOBILE_NUMBER, $username)
                    ->addAttributeToFilter(InstallData::COUNTRY_CODE, $countryCode)
                    ->getFirstItem();
                $data = $collection->getData();
                if ($data) {
                    $username = $data['email'];
                } else {
                    throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
                }
            }
        }
        return [$username, $password];
    }
}
