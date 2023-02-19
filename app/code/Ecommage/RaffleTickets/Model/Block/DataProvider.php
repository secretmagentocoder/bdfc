<?php
/**
 * @package Ceymox_ImageGallery
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Model\Block;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Ecommage\RaffleTickets\Model\ResourceModel\RaffleTickets\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
   
     /**
      * @var loadedData
      */
    protected $loadedData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param string                $name
     * @param string                $primaryFieldName
     * @param string                $requestFieldName
     * @param CollectionFactory     $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @param array                 $meta
     * @param array                 $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->collection = $this->collectionFactory->create();
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $data) {
            $requestData = $data->getData();
            $albumid = $data->getId();
            //$requestData['dynamic_rows'] = $this->getImages($albumid);
            $this->loadedData[$data->getId()] = $requestData;
        }
        return $this->loadedData;
    }
}
