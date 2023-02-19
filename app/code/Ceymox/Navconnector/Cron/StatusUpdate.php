<?php
/**
 * 
 * @package Ceymox_Navconnector
 */
declare(strict_types=1);

namespace Ceymox\Navconnector\Cron;

use Psr\Log\LoggerInterface;
use Ceymox\Navconnector\Model\Curl\CurlOperations;
use Ceymox\Navconnector\Model\ConfigProvider;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory; 
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory as StatusCollection;

class StatusUpdate {

    /**
     * @var CurlOperations
     */
    protected $curl;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var StatusCollection
     */
    protected $orderStatusCollectionFactory;

    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * Data constructor.
     *
     * @param CurlOperations $curl
     * @param ConfigProvider $configProvider
     * @param StatusCollection $orderStatusCollectionFactory
     * @param CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        CurlOperations $curl,
        ConfigProvider $configProvider,
        StatusCollection $orderStatusCollectionFactory,
        CollectionFactory $orderCollectionFactory 
    ) {
        $this->curl = $curl;
        $this->configProvider = $configProvider;
        $this->orderStatusCollectionFactory = $orderStatusCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * 
     * @return void
     */
    public function execute() {
        if ($this->configProvider->isEnableStatusUpdateCron()) {
            $statusArray = [];
            $i = 0;
            $statusCollection = $this->orderStatusCollectionFactory->create()->joinStates();
            foreach ($statusCollection as $item) {
                $statusArray[$i]['state'] = $item->getData('state');
                $statusArray[$i]['status'] = $item->getData('status');
                $statusArray[$i]['label'] = $item->getData('label');
                $i++;
            }
            
            $collection = $this->orderCollectionFactory->create()
                                ->addAttributeToSelect('*')
                                ->setPageSize(50)
                                ->addFieldToFilter('status', ['nin' => ['closed', 'complete', 'canceled']]);
            
            foreach($collection as $order){
                $incrementId = $order->getIncrementId();
                $response = $this->curl->makeRequest('WebShopNCollect', ['Receipt_No' => $incrementId]);
                foreach ($response['value'] as $key => $orderResponse) {
                    $statusKey = null;
                    $navStatus = $this->getNavStatus($orderResponse['Status']);
                    $statusKey = array_search($navStatus, array_column($statusArray, 'label'));
                    if (isset($statusKey) && ($order->getStatus() == $statusArray[$statusKey]['state'])) {
                        $order->setState($statusArray[$statusKey]['state'])->setStatus($statusArray[$statusKey]['status']);
                        $order->save();
                    }
                }                              
            }
        }
    }

    /**
     * Get correct nav status
     *
     * @param string $status
     * @return string
     */
    public function getNavStatus($status)
    {
        switch ($status) {
            case 'Cancelled':
                $navStatus = 'Canceled';
                break;
            
            default:
                $navStatus = $status;
                break;
        }
        return $navStatus;
    }
}
