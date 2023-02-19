<?php

namespace Ampersand\DisableStockReservation\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Ampersand\DisableStockReservation\Model\ResourceModel\Sources;
use Ampersand\DisableStockReservation\Api\Data\SourcesInterface;
use Ampersand\DisableStockReservation\Api\SourcesRepositoryInterface;
use Ampersand\DisableStockReservation\Api\Data\SourcesInterfaceFactory;
use Ampersand\DisableStockReservation\Model\Sources as SourcesModel;
use Ampersand\DisableStockReservation\Service\SourcesConverter;
use Magento\InventorySourceSelection\Model\Result\SourceSelectionItem;
use Magento\Framework\Model\AbstractModel;

/**
 * Class SourcesRepository
 * @package Ampersand\DisableStockReservation\Model
 */
class SourcesRepository implements SourcesRepositoryInterface
{
    /**
     * @var SourcesInterfaceFactory
     */
    protected $sourcesFactory;

    /**
     * @var Sources
     */
    protected $sourcesResourceModel;

    /**
     * @var SourcesConverter
     */
    private $sourcesConverter;

    /**
     * SourcesRepository constructor.
     * @param SourcesInterfaceFactory $sourcesFactory
     * @param Sources $sourcesResourceModel
     * @param SourcesConverter $sourcesConverter
     */
    public function __construct(
        SourcesInterfaceFactory $sourcesFactory,
        Sources $sourcesResourceModel,
        SourcesConverter $sourcesConverter
    ) {
        $this->sourcesFactory = $sourcesFactory;
        $this->sourcesResourceModel = $sourcesResourceModel;
        $this->sourcesConverter = $sourcesConverter;
    }

    /**
     * @param string $orderId
     *
     * @return SourcesInterface
     * @throws NoSuchEntityException
     */
    public function getByOrderId(string $orderId): SourcesInterface
    {
        /** @var SourcesModel $sourcesModel */
        $sourcesModel = $this->sourcesFactory->create();

        $this->sourcesResourceModel->load(
            $sourcesModel,
            $orderId,
            SourcesInterface::ORDER_ID_KEY
        );

        if (!$sourcesModel->getId()) {
            throw new NoSuchEntityException(__('Source model with the order ID "%1" does not exist', $orderId));
        }

        return $sourcesModel;
    }

    /**
     * @param string $orderId
     * @param string $itemSku
     *
     * @return SourceSelectionItem
     * @throws NoSuchEntityException
     */
    public function getSourceItemBySku(string $orderId, string $itemSku): SourceSelectionItem
    {
        $sourceSelectionItems = $this->sourcesConverter->convertSourcesJsonToSourceSelectionItems(
            $this->getByOrderId($orderId)->getSources()
        );

        if (!array_key_exists($itemSku, $sourceSelectionItems)) {
            throw new NoSuchEntityException(__('Source model with the sku "%1" does not exist', $itemSku));
        }

        return $sourceSelectionItems[$itemSku];
    }

    /**
     * @param SourcesInterface $model
     *
     * @return SourcesInterface
     * @throws CouldNotSaveException
     */
    public function save(SourcesInterface $model): SourcesInterface
    {
        try {
            // We're doing this because https://github.com/phpstan/phpstan fails when trying to pass our interface to
            // the load/save method. The resource model expects an instance of AbstractDb
            if (!$model instanceof AbstractModel) {
                throw new LocalizedException(__('Expects Magento\Framework\Model\AbstractModel'));
            }
            $this->sourcesResourceModel->save($model);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $model;
    }
}
