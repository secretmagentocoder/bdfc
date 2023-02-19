<?php declare(strict_types=1);
/**
 * @package Ceymox_Navconnector
 */

 namespace Ceymox\Navconnector\Plugin;

 use Magento\Sales\Api\Data\OrderInterface;
 use Magento\Sales\Api\OrderManagementInterface;
 use Ceymox\Navconnector\Model\ConfigProvider;
 /**
  * Class OrderManagement
  */
class OrderManagement
{
    private const TOPIC_NAME = 'nav.order.create';

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

    /**
     * @param OrderManagementInterface $subject
     * @param OrderInterface           $order
     *
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPlace(
         OrderManagementInterface $subject,
         OrderInterface $result
    ) {
        if ($this->configProvider->isEnableOrderSync()) {
            try {
                $this->publisher->publish(self::TOPIC_NAME, $result->getId());
            } catch (\Exception $e) {
                $this->logger->error($e);
            }
        }
        return $result;
    }
}