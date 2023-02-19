<?php

namespace Ecommage\RaffleTickets\Cron;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\App\Emulation;
use Psr\Log\LoggerInterface;

class CreateProductApi
{

    public function __construct
    (
        \MageWorx\OptionInventory\Model\ResourceModel\Report\CollectionFactory $collectionFactory,
        LoggerInterface $logger,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $valueCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        ResourceConnection $resourceConnection,
        Emulation $emulation,
        \Ecommage\RaffleTickets\Helper\Data $helperData
    )
    {

        $this->collectionFactory = $collectionFactory;
        $this->_logger                   = $logger;
        $this->valueCollection = $valueCollection;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->resource                  = $resourceConnection;
        $this->emulation = $emulation;
        $this->helper = $helperData;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->emulation->startEnvironmentEmulation(1, Area::AREA_FRONTEND, true);
        $this->helper->setProduct($this->helper->getConfigLinkApi());

        foreach ($this->getProductCollection() as $item)
        {
            $optionId = current($this->helper->getProductOptionId($item->getId(),0));
            if ($optionId){
                $this->unsetColumn($optionId->getOptionId());
                $this->updateDelete($optionId->getOptionId());
            }
        }

        $this->helper->setTaxProducts();
        shell_exec('php bin/magento indexer:reset');
        shell_exec('php bin/magento indexer:reindex');
        $this->emulation->stopEnvironmentEmulation();
    }

    public function updateDelete($optionId)
    {
        $connection = $this->resource->getConnection();
        try {
            $connection->update(
                'catalog_product_option_type_value',
                [
                    'delete' => 0,
                ],
                [
                    'option_id' => $optionId,
                ]
            );
        }catch (\Exception $e){
            $this->_logger->error($e->getMessage());
        }

    }

    public function unsetColumn($optionId)
    {
        $connection = $this->resource->getConnection();
        try {
            $connection->delete('catalog_product_option_type_value',
                                [
                                    'option_id = ?' => $optionId,
                                    'disable = ?'   => 0,
                                ]
            );
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }

    public function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToFilter('is_check_raffle',1);
        return $collection;
    }

    public function getOptionValue($sku)
    {
        return $this->valueCollection->create()
            ->addFieldToFilter('sku',$sku)->getData();
    }
}
