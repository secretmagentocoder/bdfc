<?php

namespace Ecommage\RaffleTickets\Cron;

use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\App\Emulation;
use Psr\Log\LoggerInterface;

/**
 *
 */
class CronUpdate
{
    const PATH = 'ecommage/create_virtual_product';

    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var ResourceConnection
     */
    protected $resource;
    /**
     * @var \Ecommage\RaffleTickets\Helper\Data
     */
    protected $helper;

    /**
     * @param Emulation                           $emulation
     * @param ResourceConnection                  $resourceConnection
     * @param \Ecommage\RaffleTickets\Helper\Data $helperData
     * @param LoggerInterface                     $logger
     */
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
        $connection = $this->resource->getConnection();
        // $select = $connection->select()
        //                      ->from(
        //                           'core_config_data','value'
        //                      )  ->where(  'path = ?', 'ecommage/create_virtual_product');
        // $data = $connection->fetchOne($select);

        // if (!empty($data))
        // {

            try {
                $this->helper->setProduct($this->helper->getConfigLinkApi());
                foreach ($this->getProductCollection() as $item)
                {
                    $optionId = current($this->helper->getProductOptionId($item->getId(),0));

                    if ($optionId){
                        $this->unsetColumn($optionId->getOptionId());
                        $this->updateDelete($optionId->getOptionId());
                    }
                }
                $this->delete();
            }catch (\Exception $e)
            {
                $this->_logger->error($e->getMessage());
            }
        // }

        $this->emulation->stopEnvironmentEmulation();
        $this->helper->setTaxProducts();
        shell_exec('php bin/magento indexer:reset');
        shell_exec('php bin/magento indexer:reindex');
        shell_exec('php bin/magento cache:clean');
    }

    /**
     * @return void
     */
    public function delete()
    {

        $connection = $this->resource->getConnection();
        try {
            $connection->update('core_config_data', ['path' => self::PATH]
            );
        }catch (\Exception $exception)
        {
            $this->_logger->error($exception->getMessage());
        }

    }

    public function updateDelete($optionId)
    {
        $connection = $this->resource->getConnection();
        $connection->update(
            'catalog_product_option_type_value',
            [
                'delete' => 0,
            ],
            [
                'option_id' => $optionId,
            ]
        );
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
