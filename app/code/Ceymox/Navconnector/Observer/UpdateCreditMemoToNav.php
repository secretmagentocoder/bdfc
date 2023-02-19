<?php
/**
 * 
 * @package Ceymox_Navconnector
 */
declare(strict_types=1);

namespace Ceymox\Navconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Ceymox\Navconnector\Model\ConfigProvider;

class UpdateCreditMemoToNav implements ObserverInterface
{
    private const TOPIC_NAME = 'nav.creditmemo.create';

    private $publisher;

    private $logger;
    
    /**
     * Construct function
     *
     * @param \Magento\Framework\MessageQueue\Publisher $publisher
     * @param ConfigProvider $configProvider
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
    	\Magento\Framework\MessageQueue\Publisher $publisher,
        ConfigProvider $configProvider,
        \Psr\Log\LoggerInterface $logger
    ) {
    	$this->publisher = $publisher;
        $this->configProvider = $configProvider;
        $this->logger = $logger;       
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->configProvider->isEnableCreditMemoSync()) {
                $creditMemo = $observer->getEvent()->getCreditmemo();
                if ($creditMemo->getId()) {               
                    $this->publisher->publish(self::TOPIC_NAME, $creditMemo->getId());                    
                }  
            }
        } catch (\Exception $e) {
            throw new \Exception ($e->getMessage());
        }
    }
}