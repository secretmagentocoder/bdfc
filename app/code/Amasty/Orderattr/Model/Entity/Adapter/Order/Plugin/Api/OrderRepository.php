<?php

namespace Amasty\Orderattr\Model\Entity\Adapter\Order\Plugin\Api;

/**
 * For API. Extension Attributes Save Get
 */
class OrderRepository
{
    /**
     * @var \Amasty\Orderattr\Model\Entity\Adapter\Order\Adapter
     */
    private $orderAdapter;

    public function __construct(
        \Amasty\Orderattr\Model\Entity\Adapter\Order\Adapter $orderAdapter
    ) {
        $this->orderAdapter = $orderAdapter;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface      $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $this->orderAdapter->addExtensionAttributesToOrder($order);

        return $order;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult
     *
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        $searchResult
    ) {
        foreach ($searchResult->getItems() as $order) {
            $this->orderAdapter->addExtensionAttributesToOrder($order);
        }

        return $searchResult;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface      $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $this->orderAdapter->saveOrderValues($order);

        return $order;
    }
}
