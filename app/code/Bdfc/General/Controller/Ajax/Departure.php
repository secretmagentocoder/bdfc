<?php
namespace Bdfc\General\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Bdfc\General\Model\AdditionalDataConfigProvider;

class Departure extends Action
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $date;

    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    private $checkoutSession;
    
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Bdfc\General\Model\AdditionalDataConfigProvider
     */
    private $additionalDataConfigProvider;

    /**
     * Constructor
     *
     * @param Context $context
     * @param LoggerInterface $logger
     * @param dateTimeFactory $dateTimeFactory
     * @param date $date
     * @param Session $checkoutSession
     * @param CollectionFactory $orderCollectionFactory
     * @param JsonFactory $resultJsonFactory
     * @param AdditionalDataConfigProvider $additionalDataConfigProvider
     */

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        dateTimeFactory $dateTimeFactory,
        TimezoneInterface $date,
        Session $checkoutSession,
        CollectionFactory $orderCollectionFactory,
        JsonFactory $resultJsonFactory,
        AdditionalDataConfigProvider $additionalDataConfigProvider
    ) {  
        $this->logger = $logger;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->date = $date;
        $this->checkoutSession = $checkoutSession;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->additionalDataConfigProvider = $additionalDataConfigProvider;
        parent::__construct($context);
    }

    /**
     * Order Restriction and Time limit for ordering
     */
    public function execute()
    {     
        try {
            $enabledOrderRestriction = $this->additionalDataConfigProvider->isEnabledOrderRestriction();
            $enabledAllowPlaceOrder = $this->additionalDataConfigProvider->isEnabledAllowPlaceOrder();
            if ($enabledOrderRestriction == 1 || $enabledAllowPlaceOrder == 1) {
                $quote = $this->checkoutSession->getQuote();
                $dateModel = $this->dateTimeFactory->create();
                $arrivalFlightDate = $dateModel->gmtDate('Y-m-d', $this->getRequest()->getParam('arrival_flight_date'));
                $arrivalFlightTime = $dateModel->gmtDate('H:i:s', $this->getRequest()->getParam('arrival_flight_time'));
                $customerEmail = $quote->getShippingAddress()->getEmail();
                $orders = $this->orderCollectionFactory->create()->addFieldToSelect('*')
                ->addFieldToFilter('customer_email', $customerEmail)->setOrder('created_at', 'desc');
           
                $resultJson = $this->resultJsonFactory->create();
                $blockTime = $this->additionalDataConfigProvider->getOrderRestrictionHour();         
                $blockTime = (float)$blockTime;
                $currentDate = $this->date->date()->format('Y-m-d H:i:s');
                $combinedDate = date('Y-m-d H:i:s', strtotime("$arrivalFlightDate $arrivalFlightTime"));
                $hourDiff = ceil((strtotime($combinedDate) - strtotime($currentDate))/3600);
                $hourDiff = (float)$hourDiff;
                $success = true;
                if ($enabledOrderRestriction == 1) {
                    foreach ($orders as $order) {
                        if ($order->getArrivalFlightDate() == $this->getRequest()->getParam('arrival_flight_date') &&  $hourDiff >= $blockTime) {
                            $success = false;
                            $message = __('You can\'t make multiple orders before %1 hours of flight on the same date.', $blockTime);
                            return $resultJson->setData([
                                'message' => $message,
                                'success' => $success
                            ]);
                        }
                        break;
                    }
                } 
                if ($enabledAllowPlaceOrder == 1) {
                    $hoursBeforeFlight = $this->additionalDataConfigProvider->getOrderAllowTime();         
                    $hoursBeforeFlight = (float)$hoursBeforeFlight;
                    $combinedDate = date('Y-m-d H:i:s', strtotime("$arrivalFlightDate $arrivalFlightTime"));
                    $hourDiff = ceil((strtotime($combinedDate) - strtotime($currentDate))/3600);
                    $hourDiff = (float)$hourDiff;
        
                    if ($hourDiff <= $hoursBeforeFlight) {
                        $success = false;
                        $message = __('Need to make orders before %1 hours of flight', $hoursBeforeFlight);
                        return $resultJson->setData([
                            'message' => $message,
                            'success' => $success
                        ]);
                    } 
                } 
            }
            return $resultJson->setData([
                'message' => "success",
                'success' => $success
            ]);

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
