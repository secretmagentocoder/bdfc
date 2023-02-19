<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Ecommage\RaffleTickets\Model\RaffleTicketsFactory;
use Ecommage\RaffleTickets\Api\Data\RaffleTicketsInterface;
use Ecommage\RaffleTickets\Model\ResourceModel\RaffleTickets;
use Ecommage\RaffleTickets\Api\Data\RaffleTicketsInterfaceFactory;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Ecommage\RaffleTickets\Model\ResourceModel\RaffleTickets\CollectionFactory;

class RaffleTicketsRepository implements \Ecommage\RaffleTickets\Api\RaffleTicketsRepositoryInterface
{
    /**
     * @var RaffleTicketsFactory
     */
    private $raffleTicketsFactory;
    /**
     * @var ResourceModel\RaffleTickets
     */
    private $raffleTicketsResource;
    /**
     * @var \Ecommage\RaffleTickets\Api\Data\RaffleTicketsInterfaceFactory
     */
    private $raffleTicketsDataFactory;
    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;
    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;
    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * RaffleTicketsRepository constructor.
     *
     * @param RaffleTicketsFactory $raffleTicketsFactory
     * @param RaffleTickets $raffleTicketsResource
     * @param RaffleTicketsInterfaceFactory $raffleTicketsDataFactory
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     * @param DataObjectHelper $dataObjectHelper
     * @param SearchResultsInterfaceFactory $searchResultFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        RaffleTicketsFactory $raffleTicketsFactory,
        RaffleTickets $raffleTicketsResource,
        RaffleTicketsInterfaceFactory $raffleTicketsDataFactory,
        ExtensibleDataObjectConverter $dataObjectConverter,
        DataObjectHelper $dataObjectHelper,
        SearchResultsInterfaceFactory $searchResultFactory,
        DataObjectProcessor $dataObjectProcessor,
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $collectionFactory
    ) {
        $this->raffleTicketsFactory = $raffleTicketsFactory;
        $this->raffleTicketsResource = $raffleTicketsResource;
        $this->raffleTicketsDataFactory = $raffleTicketsDataFactory;
        $this->dataObjectConverter = $dataObjectConverter;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->searchResultFactory = $searchResultFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Function Get By Id
     *
     * @param  int $id
     * @return RaffleTicketsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id)
    {
        $raffleTicketsObj = $this->raffleTicketsFactory->create();
        $this->raffleTicketsResource->load($raffleTicketsObj, $id);
        if (!$raffleTicketsObj->getId()) {
            throw new NoSuchEntityException(__('Item with id "%1" does not exist.', $id));
        }
        $data = $this->raffleTicketsDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $data,
            $raffleTicketsObj->getData(),
            RaffleTicketsInterface::class
        );
        $data->setId($raffleTicketsObj->getId());

        return $data;
    }

    /**
     * Save RaffleTickets Data
     *
     * @param  String $raffleTickets
     * @return RaffleTicketsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(RaffleTicketsInterface $raffleTickets)
    {
        try {
            /**
             * @var RaffleTicketsInterface|\Magento\Framework\Model\AbstractModel $data
             */
            $this->raffleTicketsResource ->save($raffleTickets);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the data: %1',
                    $exception->getMessage()
                )
            );
        }
        return $raffleTickets;
    }

    /**
     * Delete the raffleTickets by raffleTickets id
     *
     * @param Int $raffleTicketsId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($raffleTicketsId)
    {
        $raffleTicketsObj = $this->raffleTicketsFactory->create();
        $this->raffleTicketsResource->load($raffleTicketsObj, $raffleTicketsId);
        $this->raffleTicketsResource->delete($raffleTicketsObj);
        if ($raffleTicketsObj->isDeleted()) {
            return true;
        }
        return false;
    }

    /**
     * Get List
     *
     * @param  SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
