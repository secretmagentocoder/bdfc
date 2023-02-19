<?php
namespace Sparsh\MobileNumberLogin\Model\Attribute\Backend;

use Magento\Framework\Exception\LocalizedException;
use Sparsh\MobileNumberLogin\Setup\InstallData;

/**
 * Class MobileNumber
 * @package Sparsh\MobileNumberLogin\Model\Attribute\Backend
 */
class MobileNumber extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * MobileNumber constructor.
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * Validates if the customer mobile number is unique.
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     * @throws LocalizedException
     */
    public function beforeSave($object)
    {
        $mobileNumber = $object->getData(InstallData::MOBILE_NUMBER);
        $countryCode = $object->getData(InstallData::COUNTRY_CODE);
        if ($mobileNumber && $countryCode) {
            $collection = $this->customerCollectionFactory->create()
                ->addAttributeToFilter(InstallData::MOBILE_NUMBER, $mobileNumber)
                ->addAttributeToFilter(InstallData::COUNTRY_CODE, $countryCode);
            if ($object->getSharingConfig()->isWebsiteScope()) {
                $collection->addAttribuTeToFilter('website_id', (int) $object->getData('website_id'));
            }
            if ($object->getData('entity_id')) {
                $collection->addAttribuTeToFilter('entity_id', ['neq' => (int) $object->getData('entity_id')]);
            }
            if ($collection->getSize() > 0) {
                throw new LocalizedException(
                    __('A customer with the same mobile number already exists in an associated website.')
                );
            }
        }
        return parent::beforeSave($object);
    }
}
