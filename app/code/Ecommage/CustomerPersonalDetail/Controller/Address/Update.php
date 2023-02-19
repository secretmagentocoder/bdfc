<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ecommage\CustomerPersonalDetail\Controller\Address;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Helper\Data as HelperData;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NotFoundException;
use Psr\Log\LoggerInterface;


/**
 * Customer Address Form Post Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Update extends \Magento\Customer\Controller\Address implements HttpPostActionInterface
{
    private $logger;
    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Mapper
     */
    private $customerAddressMapper;

    /**
     * @var Filesystem
     */
    private $filesystem;

    private $addressRepository;

    protected $regionDataFactory;

    public function __construct(
        Context                    $context,
        Session                    $customerSession,
        FormKeyValidator           $formKeyValidator,
        FormFactory                $formFactory,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory    $addressDataFactory,
        RegionInterfaceFactory     $regionDataFactory,
        DataObjectProcessor        $dataProcessor,
        DataObjectHelper           $dataObjectHelper,
        ForwardFactory             $resultForwardFactory,
        PageFactory                $resultPageFactory,
        RegionFactory              $regionFactory,
        HelperData                 $helperData,
        LoggerInterface            $logger,
        Filesystem                 $filesystem = null
    )
    {
        $this->regionDataFactory = $regionDataFactory;
        $this->logger = $logger;
        $this->addressRepository = $addressRepository;
        $this->regionFactory = $regionFactory;
        $this->helperData = $helperData;
        $this->filesystem = $filesystem ?: ObjectManager::getInstance()->get(Filesystem::class);
        parent::__construct(
            $context,
            $customerSession,
            $formKeyValidator,
            $formFactory,
            $addressRepository,
            $addressDataFactory,
            $regionDataFactory,
            $dataProcessor,
            $dataObjectHelper,
            $resultForwardFactory,
            $resultPageFactory
        );
    }

    /**
     * Process address form save
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $addressData = $this->getRequest()->getParams();
            $customerId = $this->_customerSession->getCustomerId();
            $address = $this->_extractAddress();
            if ($addressData['address_id']) {
                $address = $this->addressRepository->getById($addressData['address_id'])
                    ->setCustomerId($customerId)
                    ->setStreet($addressData['street'])
                    ->setCity($addressData['city'])
                    ->setTelephone($addressData['telephone'])
                    ->setPostcode($addressData['postcode'])
                    ->setCountryId($addressData['country_id']);
            }
            if (isset($addressData['default_shipping'])) {
                $address->setIsDefaultShipping(true);
            } else {
                $address->setIsDefaultShipping(false);
            }
            $this->addressRepository->save($address);
            $this->messageManager->addSuccess(__('You saved the address.'));
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addErrorMessage($error->getMessage());
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addExceptionMessage($e, __('We can\'t save the address.'));
        }

        return $this->resultRedirectFactory->create()->setPath('ecommage_customer_update/account');
    }

    /**
     * Get Customer Address Mapper instance
     *
     * @return Mapper
     *
     * @deprecated 100.1.3
     */
    private function getCustomerAddressMapper()
    {
        if ($this->customerAddressMapper === null) {
            $this->customerAddressMapper = ObjectManager::getInstance()->get(
                \Magento\Customer\Model\Address\Mapper::class
            );
        }
        return $this->customerAddressMapper;
    }

    /**
     * Extract address from request
     *
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    protected function _extractAddress()
    {
        $existingAddressData = $this->getExistingAddressData();

        /** @var \Magento\Customer\Model\Metadata\Form $addressForm */
        $addressForm = $this->_formFactory->create(
            'customer_address',
            'customer_address_edit',
            $existingAddressData
        );
        $addressData = $addressForm->extractData($this->getRequest());
        $attributeValues = $addressForm->compactData($addressData);

        $this->updateRegionData($attributeValues);

        $addressDataObject = $this->addressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            array_merge($existingAddressData, $attributeValues),
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        $addressDataObject->setCustomerId($this->_getSession()->getCustomerId())
            ->setIsDefaultBilling(
                $this->getRequest()->getParam(
                    'default_billing',
                    isset($existingAddressData['default_billing']) ? $existingAddressData['default_billing'] : false
                )
            )
            ->setIsDefaultShipping(
                $this->getRequest()->getParam(
                    'default_shipping',
                    isset($existingAddressData['default_shipping']) ? $existingAddressData['default_shipping'] : false
                )
            );

        return $addressDataObject;
    }

    protected function getExistingAddressData()
    {
        $existingAddressData = [];
        if ($addressId = $this->getRequest()->getParam('id')) {
            $existingAddress = $this->_addressRepository->getById($addressId);
            if ($existingAddress->getCustomerId() !== $this->_getSession()->getCustomerId()) {
                throw new NotFoundException(__('Address not found.'));
            }
            $existingAddressData = $this->getCustomerAddressMapper()->toFlatArray($existingAddress);
        }
        return $existingAddressData;
    }

    protected function updateRegionData(&$attributeValues)
    {
        if (!empty($attributeValues['region_id'])) {
            $newRegion = $this->regionFactory->create()->load($attributeValues['region_id']);
            $attributeValues['region_code'] = $newRegion->getCode();
            $attributeValues['region'] = $newRegion->getDefaultName();
        }

        $regionData = [
            RegionInterface::REGION_ID => !empty($attributeValues['region_id']) ? $attributeValues['region_id'] : null,
            RegionInterface::REGION => !empty($attributeValues['region']) ? $attributeValues['region'] : null,
            RegionInterface::REGION_CODE => !empty($attributeValues['region_code'])
                ? $attributeValues['region_code']
                : null,
        ];

        $region = $this->regionDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $region,
            $regionData,
            \Magento\Customer\Api\Data\RegionInterface::class
        );
        $attributeValues['region'] = $region;
    }
}
