<?php

namespace Ecommage\CustomerPersonalDetail\Controller\Address;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\InputException;

class Save extends \Magento\Customer\Controller\Address implements HttpPostActionInterface
{
    /**
     * @var AddressInterfaceFactory
     */
    private $dataAddressFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator, \Magento\Customer\Model\Metadata\FormFactory $formFactory, \Magento\Customer\Api\AddressRepositoryInterface $addressRepository, \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory, \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory, \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor, \Magento\Framework\Api\DataObjectHelper $dataObjectHelper, \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->addressRepository = $addressRepository;
        $this->dataAddressFactory = $addressDataFactory;
        parent::__construct($context, $customerSession, $formKeyValidator, $formFactory, $addressRepository, $addressDataFactory, $regionDataFactory, $dataProcessor, $dataObjectHelper, $resultForwardFactory, $resultPageFactory);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $redirectUrl = 'customer/address/new';
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath($redirectUrl);
        }

        try {
            $formData = $this->getRequest()->getParams();
            $address = $this->dataAddressFactory->create();
            $customerId = $this->_customerSession->getCustomerId();
            $deliverAt = $this->getRequest()->getParam('deliver_at', null);
            
            if($this->getRequest()->getParam('address_id')) $address = $this->addressRepository->getById($formData['address_id']);
            
            $address->setCustomerId($customerId)
                ->setFirstname($formData['firstname'])
                ->setLastname($formData['lastname'])
                ->setStreet($formData['street'])
                ->setPostcode($formData['postcode'])
                ->setCity($formData['city'])
                ->setTelephone($formData['mobile_number'])
                ->setCountryId($formData['country_id']);
            $address->setCustomAttribute('deliver_at', $deliverAt);

                if (isset($formData['default_shipping'])) {
                    $address->setIsDefaultShipping(true);
                } else {
                    $address->setIsDefaultShipping(false);
                }
                if (isset($formData['default_billing'])) {
                    $address->setIsDefaultBilling(true);
                } else {
                    $address->setIsDefaultBilling(false);
                }
            $this->addressRepository->save($address);
            $this->messageManager->addSuccess(__('You saved the address.'));
           $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('customer/address');
            return $resultRedirect;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addErrorMessage($error->getMessage());
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t save the address.'));
        }

        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->error($redirectUrl));
    }
}
