<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_MinMaxQtyOrderPerCate
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MinMaxQtyOrderPerCate\Override\Controller\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Bss\MinMaxQtyOrderPerCate\Helper\Data;
use Bss\MinMaxQtyOrderPerCate\Helper\OverviewPost as OverviewPostHelper;

class OverviewPost extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Checkout\Api\AgreementsValidatorInterface
     */
    protected $agreementsValidator;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var OverviewPostHelper
     */
    private $overviewPostHelper;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Checkout\Api\AgreementsValidatorInterface $agreementValidator
     * @param SessionManagerInterface $session
     * @param OverviewPostHelper $overviewPostHelper
     * @param Data $helper
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Api\AgreementsValidatorInterface $agreementValidator,
        SessionManagerInterface $session,
        OverviewPostHelper $overviewPostHelper,
        \Magento\Framework\Registry $registry,
        Data $helper
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->logger = $logger;
        $this->agreementsValidator = $agreementValidator;
        $this->session = $session;
        $this->helper = $helper;
        $this->overviewPostHelper = $overviewPostHelper;
        $this->registry = $registry;

        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement
        );
    }

    /**
     * Overview action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->helper->versionCompare('2.4.1')) {
            $this->overviewPostHelper->getPaymentRateLimiterObject()->limit();
        } else {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                $this->_forward('backToAddresses');
                return;
            }
        }

        if (!$this->_validateMinimumAmount()) {
            return;
        }

        try {
            if (!$this->agreementsValidator->isValid(array_keys($this->getRequest()->getPost('agreement', [])))) {
                $this->messageManager->addErrorMessage(
                    __('Please agree to all Terms and Conditions before placing the order.')
                );
                $this->_redirect('*/*/billing');
                return;
            }

            $payment = $this->getRequest()->getPost('payment');
            $paymentInstance = $this->_getCheckout()->getQuote()->getPayment();
            if (isset($payment['cc_number'])) {
                $paymentInstance->setCcNumber($payment['cc_number']);
            }
            if (isset($payment['cc_cid'])) {
                $paymentInstance->setCcCid($payment['cc_cid']);
            }
            $this->_getCheckout()->createOrders();
            $this->_getState()->setCompleteStep(State::STEP_OVERVIEW);

            if ($this->session->getAddressErrors()) {
                $this->_getState()->setActiveStep(State::STEP_RESULTS);
                $this->_redirect('*/*/results');
            } else {
                $this->_getState()->setActiveStep(State::STEP_SUCCESS);
                $this->_getCheckout()->getCheckoutSession()->clearQuote();
                $this->_getCheckout()->getCheckoutSession()->setDisplaySuccess(true);
                $this->_redirect('*/*/success');
            }
        } catch (\Magento\Checkout\Exception $e) {
            $this->_objectManager->get(
                \Magento\Checkout\Helper\Data::class
            )->sendPaymentFailedEmail(
                $this->_getCheckout()->getQuote(),
                $e->getMessage(),
                'multi-shipping'
            );
            $this->_getCheckout()->getCheckoutSession()->clearQuote();
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('*/cart');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_objectManager->get(
                \Magento\Checkout\Helper\Data::class
            )->sendPaymentFailedEmail(
                $this->_getCheckout()->getQuote(),
                $e->getMessage(),
                'multi-shipping'
            );
            $this->messageManager->addErrorMessage($e->getMessage());
            $message = $this->registry->registry('bss_message');
            if ($e->getMessage() == $message) {
                $this->registry->unregister('bss_message');
                $this->_redirect('*/*/addresses');
            } else {
                $this->_redirect('*/*/billing');
            }
        } catch (\Exception $e) {
            if ($e instanceof \Magento\Framework\Exception\PaymentException) {
                $message = $e->getMessage();
                if (!empty($message)) {
                    $this->messageManager->addErrorMessage($message);
                }
                $this->_redirect('*/*/billing');
            }
            if ($this->helper->versionCompare('2.4.1')) {
                if ($e instanceof
                    \Magento\Checkout\Api\Exception\PaymentProcessingRateLimitExceededException
                ) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    $this->_redirect('*/*/overview');
                }
            }
            $this->logger->critical($e);
            try {
                $this->_objectManager->get(
                    \Magento\Checkout\Helper\Data::class
                )->sendPaymentFailedEmail(
                    $this->_getCheckout()->getQuote(),
                    $e->getMessage(),
                    'multi-shipping'
                );
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
            $this->messageManager->addErrorMessage(__('Order place error'));
            $this->_redirect('*/*/billing');
        }
    }
}
