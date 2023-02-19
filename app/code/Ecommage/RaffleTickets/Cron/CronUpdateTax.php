<?php

namespace Ecommage\RaffleTickets\Cron;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 *
 */
class CronUpdateTax
{
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
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        Emulation $emulation,
        \Ecommage\RaffleTickets\Helper\Data $helperData
    )
    {
        $this->productRepository = $productRepository;
        $this->_logger                   = $logger;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->emulation = $emulation;
        $this->helper = $helperData;
    }

    /**
     * @return void
     */
    public function execute()
    {
            try {
                $collection = $this->getProductCollection();
                foreach ($collection as $product)
                {
                    foreach ($this->storeManager->getStores() as $store)
                    {
                        $tax = $this->productRepository->get($product->getSku(),false,$store->getId())
                                ->setTaxClassId($this->helper->getTaxByApi($product->getSku(),$store->getName() ?? 0))
                                ->save();
                    }
                }
                shell_exec('php bin/magento indexer:reset');
                shell_exec('php bin/magento indexer:reindex');
            }catch (\Exception $e)
            {
                $this->_logger->error($e->getMessage());
            }

    }


    public function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToFilter('is_check_raffle',1)
        ->addFieldToFilter('type_id','virtual');
        return $collection;
    }
}
