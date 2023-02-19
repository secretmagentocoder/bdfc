<?php

namespace Ecommage\CustomerUpdatePassword\Controller\Update;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Phrase;

class Save extends AbstractAccount implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;
    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @param Context                                          $context
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param Session                                          $customerSession
     * @param AccountManagementInterface                       $customerAccountManagement
     * @param CustomerRepositoryInterface                      $customerRepository
     * @param Validator                                        $formKeyValidator
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        Validator $formKeyValidator
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated 100.1.0
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return ObjectManager::getInstance()->get(EmailNotificationInterface::class);
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/update/change');

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }

    /**
     * Change customer email or password action
     *
     * @return Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());
        $password = $this->getRequest()->getParam('password');
        if ($validFormKey && $this->getRequest()->isPost()) {
            $currentCustomerDataObject = $this->customerRepository->getById($this->session->getCustomerId());

            try {
                $isPasswordChanged = $this->changeCustomerPassword($currentCustomerDataObject->getEmail());

                $this->customerRepository->save($currentCustomerDataObject, $this->encryptor->getHash($password, true));
                $this->getEmailNotification()->credentialsChanged(
                    $currentCustomerDataObject,
                    $currentCustomerDataObject->getEmail(),
                    $isPasswordChanged
                );
                $this->messageManager->addSuccessMessage(__('You saved the account information.'));
                if ($isPasswordChanged) {
                    $this->session->logout();
                    $this->session->start();
                    return $resultRedirect->setPath('customer/account/login');
                }
                return $resultRedirect->setPath('*/update/change');
            }catch (UserLockedException $e) {
                $message = __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                );
                $this->session->logout();
                $this->session->start();
                $this->messageManager->addErrorMessage($message);

                return $resultRedirect->setPath('customer/account/login');
            }catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            }catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
            $this->session->setCustomerFormData($this->getRequest()->getPostValue());
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/update/change');

        return $resultRedirect;
    }

    /**
     * Change customer password
     *
     * @param string $email
     * @return boolean
     * @throws InvalidEmailOrPasswordException|InputException
     */
    protected function changeCustomerPassword($email)
    {
        $isPasswordChanged = false;
        if ($this->getRequest()->getParam('change_password')) {
            $currPass = $this->getRequest()->getPost('current_password');
            $newPass = $this->getRequest()->getPost('password');
            $confPass = $this->getRequest()->getPost('password_confirmation');
            if ($newPass != $confPass) {
                throw new InputException(__('Password confirmation doesn\'t match entered password.'));
            }

            $isPasswordChanged = $this->customerAccountManagement->changePassword($email, $currPass, $newPass);
        }

        return $isPasswordChanged;
    }
}