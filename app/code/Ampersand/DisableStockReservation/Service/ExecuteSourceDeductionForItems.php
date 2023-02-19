<?php

namespace Ampersand\DisableStockReservation\Service;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Ampersand\DisableStockReservation\Api\SourcesRepositoryInterface;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestFactory;
use Magento\InventorySourceDeductionApi\Model\ItemToDeductFactory;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionServiceInterface;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Catalog\Model\ResourceModel\Product;

/**
 * Class ExecuteSourceDeductionForItems
 * @package Ampersand\DisableStockReservation\Service
 */
class ExecuteSourceDeductionForItems
{
    /**
     * @var Processor
     */
    private $priceIndexer;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @var SalesEventInterfaceFactory
     */
    private $salesEventFactory;

    /**
     * @var ItemToDeductFactory
     */
    private $itemToDeductFactory;

    /**
     * @var SourceDeductionRequestFactory
     */
    private $sourceDeductionRequestFactory;

    /**
     * @var SourceDeductionServiceInterface
     */
    private $sourceDeductionService;

    /**
     * @var SourcesRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var Product
     */
    protected $product;

    /**
     * ExecuteSourceDeductionForItems constructor.
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     * @param ItemToDeductFactory $itemToDeductFactory
     * @param SourceDeductionRequestFactory $sourceDeductionRequestFactory
     * @param SourceDeductionServiceInterface $sourceDeductionService
     * @param SourcesRepositoryInterface $sourceRepository
     * @param Processor $priceIndexer
     * @param Product $product
     */
    public function __construct(
        WebsiteRepositoryInterface $websiteRepository,
        SalesEventInterfaceFactory $salesEventFactory,
        SalesChannelInterfaceFactory $salesChannelFactory,
        ItemToDeductFactory $itemToDeductFactory,
        SourceDeductionRequestFactory $sourceDeductionRequestFactory,
        SourceDeductionServiceInterface $sourceDeductionService,
        SourcesRepositoryInterface $sourceRepository,
        Processor $priceIndexer,
        Product $product
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->salesEventFactory = $salesEventFactory;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->itemToDeductFactory = $itemToDeductFactory;
        $this->sourceDeductionRequestFactory = $sourceDeductionRequestFactory;
        $this->sourceDeductionService = $sourceDeductionService;
        $this->sourceRepository = $sourceRepository;
        $this->priceIndexer = $priceIndexer;
        $this->product = $product;
    }

    /**
     * @param OrderItem $orderItem
     * @param array $itemsToCancel
     */
    public function executeSourceDeductionForItems(OrderItem $orderItem, array $itemsToCancel)
    {
        $order = $orderItem->getOrder();

        $websiteId = $order->getStore()->getWebsiteId();
        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
        $salesChannel = $this->salesChannelFactory->create([
            'data' => [
                'type' => SalesChannelInterface::TYPE_WEBSITE,
                'code' => $websiteCode
            ]
        ]);

        $salesEvent = $this->salesEventFactory->create([
            'type' => SalesEventInterface::EVENT_ORDER_CANCELED,
            'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
            'objectId' => (string)$orderItem->getOrderId()
        ]);

        $itemsSkus = [];

        /** @var OrderItem $item */
        foreach ($itemsToCancel as $item) {
            $itemsSkus[] = $item->getSku();

            try {
                $sourceItem = $this->sourceRepository->getSourceItemBySku(
                    (string)$order->getId(),
                    $item->getSku()
                );
                $sourceCode = $sourceItem->getSourceCode();
            } catch (NoSuchEntityException $exception) {
                $sourceCode = 'default';
            }

            $sourceDeductionRequest = $this->sourceDeductionRequestFactory->create([
                'sourceCode' => $sourceCode,
                'items' => [$this->itemToDeductFactory->create([
                    'sku' => $item->getSku(),
                    'qty' => -$item->getQuantity()
                ])],
                'salesChannel' => $salesChannel,
                'salesEvent' => $salesEvent
            ]);

            $this->sourceDeductionService->execute($sourceDeductionRequest);
        }

        $itemsIds = $this->product->getProductsIdsBySkus($itemsSkus);
        $itemsIds = array_values(array_map('intval', $itemsIds));
        $this->priceIndexer->reindexList($itemsIds);
    }
}
