<?php

namespace Ecommage\CustomerPersonalDetail\Controller\Account;

use Ecommage\CustomerPersonalDetail\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Exception;
use Psr\Log\LoggerInterface;

class  Update extends Action implements HttpPostActionInterface
{
    /**
     * @var SubscriptionManagerInterface
     */
    protected $subscriptionManager;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerInterface;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var SubscriberFactory
     */
    protected $subcriberFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @param Context                                             $context
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param StoreManagerInterface          $storeManager
     * @param LoggerInterface                                     $logger
     * @param CustomerRepositoryInterface   $customerRepository
     * @param Session                     $customerSession
     * @param SubscriptionManagerInterface                        $subscriptionManager
     * @param SubscriberFactory         $subscriberFactory
     * @param Data        $helper
     */
    public function __construct(
        Context $context,
        CustomerInterfaceFactory $customerInterfaceFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        SubscriptionManagerInterface $subscriptionManager,
        SubscriberFactory $subscriberFactory,
        Data $helper,
        Config $eavConfig
    ) {
        $this->eavConfig           = $eavConfig;
        $this->subscriptionManager = $subscriptionManager;
        $this->helper              = $helper;
        $this->subcriberFactory    = $subscriberFactory;
        $this->customerSession     = $customerSession;
        $this->_customerRepository = $customerRepository;
        $this->logger              = $logger;
        $this->storeManager        = $storeManager;
        $this->customerInterface   = $customerInterfaceFactory;
        parent::__construct($context);
    }

    /**
     * @param      $key
     * @param null $default
     *
     * @return mixed
     */
    protected function getPostValue($key, $default = null)
    {
        return $this->getRequest()->getParam($key);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        /** @var Customer $customer */
        $customer     = $this->customerSession->getCustomer();
        if (!$customer || !$customer->getId()) {
            $this->messageManager->addNotice(__('The session has expired, please login again'));
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        try {
            $customerData = $customer->getDataModel();
            $customerData->setPrefix($this->getPostValue('prefix'))
                         ->setDob($this->getPostValue('cust_dob'))
                         ->setFirstname($this->getPostValue('firstname'))
                         ->setLastname($this->getPostValue('lastname'))
                         ->setCustomAttribute('cust_dob', $this->getPostValue('cust_dob'))
                         ->setCustomAttribute('country_code', $this->getPostValue('country_code'))
                         ->setCustomAttribute('national_id', $this->getPostValue('nationality'))
                         ->setCustomAttribute('passport_no', $this->getPostValue('passport_no'))
                         ->setCustomAttribute('mobile_number', $this->getPostValue('mobile_number'));

            $this->_customerRepository->save($customerData);

            $isSubscribed = (int)$this->getPostValue('is_subscribed');
            $storeId    = $this->storeManager->getStore()->getId();
            if ($isSubscribed === 1) {
                $this->subcribe($customer->getId(), $storeId);
            } else {
                $this->unSubcribe($customer->getEmail());
            }

            $this->messageManager->addSuccess(__('Your Personal details have been updated successfully'));
        } catch (\Exception $exception) {
            $this->messageManager->addError($exception->getMessage());
        }


        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }

    /**
     * @param $email
     *
     * @return void
     * @throws \Exception
     */
    protected function unSubcribe($email)
    {
        try {
            $subscriber = $this->subcriberFactory->create()->loadByEmail($email);
            $subscriber->setStatus(Subscriber::STATUS_UNSUBSCRIBED);
            $subscriber->save();
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e, $e->getMessage());
        }
    }

    /**
     * @param $customerId
     * @param $storeId
     *
     * @return void
     */
    protected function subcribe($customerId, $storeId)
    {
        try {
            $this->subscriptionManager->subscribeCustomer($customerId, $storeId);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e, $e->getMessage());
        }
    }
}
