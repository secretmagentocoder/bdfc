<?php

namespace Ecommage\ChangeDeliveryDate\Controller\Status;

use DateTime;
use Ecommage\ChangeDeliveryDate\Helper\Data;
use Ecommage\ChangeDeliveryDate\Block\Widget\DeliveryForm;
use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Update extends Action
{
    /**
     * @var Data
     */
    protected $helperData;
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var Validator
     */
    protected $formKeyValidator;
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Check constructor.
     *
     * @param Context                             $context
     * @param Data                                $helperData
     * @param Validator                           $formKeyValidator
     * @param PageFactory                         $resultPageFactory
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\ResourceModel\Order $orderResourceModel
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        Data $helperData,
        Session $customerSession,
        Validator $formKeyValidator,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order $orderResourceModel,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->helperData        = $helperData;
        $this->formKeyValidator  = $formKeyValidator;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession   = $customerSession;
        $this->httpContext       = $httpContext;
        $this->orderResourceModel = $orderResourceModel;
        $this->orderRepository = $orderRepository;
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function isLogin()
    {
        return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * @return array|ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        if (!$this->isLogin()) {
            $message = __('The session has expired, please login again');
            return $resultJson->setData(
                [
                    'status'  => 'error',
                    'message' => $message
                ]
            );
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $message = __('Invalid Form Key. Please refresh the page.');
            return $resultJson->setData(
                [
                    'status'  => 'error',
                    'message' => $message
                ]
            );
        }

        $mobileNo  = $this->getRequest()->getParam('mobile_no');
        $receiptNo = $this->getRequest()->getParam('receipt_no');
        $response = $this->processApi();
        $message  = $response['message'];
        return $resultJson->setData($response);
    }

    /**
     * @return array
     */
    public function processApi()
    {
        $type      = $this->getRequest()->getParam('type');
        $mobileNo  = $this->getRequest()->getParam('mobile_no');
        $receiptNo = $this->getRequest()->getParam('receipt_no');
        $newDate = $this->getRequest()->getParam('new_date');
        $newDate = $newDate. ' ' .'00:00:00';
        list($deliveryDate, $deliveryTime) = explode(' ', $newDate);
        $deliveryDate = date('Y-m-d', strtotime($deliveryDate));
        if (!$this->validateDate($deliveryDate)) {
            return [
                    'status'  => 'error',
                    'message' => __('Update date format incorrect.')
                ];
            }
        $response = $this->helperData->changeSCStatusPOSCheck($receiptNo, $mobileNo);
        $message = $response['message'][0];
        if (isset($message) && $message == 'Transaction Found') {
            $response = $this->helperData->changeSCStatusPOS($receiptNo, $mobileNo, $deliveryDate, $deliveryTime);
            return $this->checkStatusWithMessage($response);
        } else {
            return [
                'status'  => 'error',
                'message' => __('Transaction Not Found.')
            ];
        }
    }

    /**
     * @param $response
     *
     * @return array
     */
    protected function checkStatusWithMessage($response)
    {
        $status    = $response['status'] ?? null;
        $mail  = 0 ;
        $newDate   = $this->getRequest()->getParam('new_date',null);
        $mobileNo  = $this->getRequest()->getParam('mobile_no',null);
        $receiptNo = $this->getRequest()->getParam('receipt_no',null);
        $oldDate = $this->getRequest()->getParam('old_date',null);
        $successMessage = __('Delivery date updated successfully.');
        if ($status == 'success') {
            $message = $response['message'][0] ?? '';
            if ($response['message'][0] == 'Date Changed' ) {
                $orderModel = $this->orderFactory->create();
                $order = $orderModel->loadByIncrementId($receiptNo);
                $orderId = $order->getId();
                if(!empty($orderId)) {
                    $order = $this->orderRepository->get($orderId);
                    $order->setCollectionDate($newDate);
                    $this->orderResourceModel->save($order);
                    $customer     = $this->customerSession->getCustomer();
                    $customerData = $this->customerSession->getCustomerData();
                    $customerData = [
                        'customer_prefix'    => $customer->getData('prefix'),
                        'customer_firstname' => $customer->getData('firstname'),
                        'customer_lastname'  => $customer->getData('lastname'),
                        'customer_email'     => $customer->getEmail(),
                        'customer_name'      => $customer->getName(),
                    ];
                    $data = [
                        'mobile_no'  => $mobileNo,
                        'receipt_no' => $receiptNo,
                        'new_date'   => $newDate,
                        'status'     => 'success',
                        'message'    => $message,
                        'display'    => 1
                    ];
                    $mail = 1 ;
                    $this->helperData->sendEmail(array_merge($data, $customerData), $mail);
                    return [
                        'status'  => 'success',
                        'message' => $successMessage
                    ];
                }
            }
        }
        return [
            'status'  => 'success',
            'message' => $successMessage
        ];
    }

    /**
     * @param        $date
     * @param string $format
     *
     * @return bool
     */
    protected function validateDate($date, $format = 'Y-m-d'): bool
    {
        try {
            $d = DateTime::createFromFormat($format, $date);
            // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
            return $d && $d->format($format) === $date;
        } catch (Exception $e) {
            return false;
        }
    }
}
