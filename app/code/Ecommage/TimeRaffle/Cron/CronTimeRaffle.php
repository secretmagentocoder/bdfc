<?php

namespace Ecommage\TimeRaffle\Cron;

use \Magento\Store\Model\ScopeInterface;

class CronTimeRaffle
{
    public function __construct
    (
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Ecommage\CheckoutData\Helper\Data $helper,
        \Magento\Store\Model\App\Emulation $emulation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->_logger = $logger;
        $this->helper = $helper;
        $this->emulation = $emulation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;

    }

    public function execute()
    {

        $this->emulation->startEnvironmentEmulation(1, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        try {
           $this->getItemCart();

        }catch (\Exception $e)
        {
        
            $this->_logger->error($e->getMessage());
        }
        shell_exec('php bin/magento cache:clean');
        $this->emulation->stopEnvironmentEmulation();
    }

    protected function getItemCart()
    {
        $collection = $this->getCollectionQuote();
        foreach ($collection as $item) {           
            $storeId = $item->getStoreId();
            $enable = $this->scopeConfig->getValue("raffle_tickets/general/enabled",
                            ScopeInterface::SCOPE_STORE,
                            $storeId
                        );

            if ($enable) {
                $reservationTime = $this->scopeConfig->getValue("raffle_tickets/general/reservation_time",
                                    ScopeInterface::SCOPE_STORE,
                                    $storeId
                                   );
                $reservationTime = $reservationTime * 60;
                if (! $reservationTime) {
                    continue;
                }
                if ($item->getAllVisibleItems()) {
                    foreach ($item->getAllVisibleItems() as $itemQuote) {
                        $isCheck = $this->isCheckRemove($itemQuote->getCartTimeRaffle(), $reservationTime);
                        if (!empty($itemQuote->getCartTimeRaffle()) && !empty($isCheck)) {
                            $item->removeItem($itemQuote->getItemId());
                            $item->getBillingAddress();
                            $item->getShippingAddress()->setCollectShippingRates(true);
                            $item->collectTotals();
                            $item->save();
                        }
                    }
                }
            }
        }
        return $this;
    }

    protected function isCheckRemove($time, $reservationTime)
    {
        $currentTime = $this->helper->getTimeLocal();
        $date = strtotime($currentTime) - strtotime($time);
        if ($date == $reservationTime || $date > $reservationTime)
        {
            return true;
        }
        return false;
    }

    public function getCollectionQuote()
    {
        return $this->quoteCollectionFactory->create()
            ->addFieldToFilter('is_active', 1);
    }

}