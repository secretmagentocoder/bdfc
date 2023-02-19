<?php

namespace Ecommage\CreateProductApi\Cron;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\App\Emulation;
use Psr\Log\LoggerInterface;

class UploadHexCode
{

    public function __construct
    (
        ResourceConnection $resourceConnection,
        Emulation $emulation,
        \Ecommage\CreateProductApi\Helper\Data $helper,
        LoggerInterface $logger
    )
    {
        $this->_logger                   = $logger;
        $this->resource                  = $resourceConnection;
        $this->emulation = $emulation;
        $this->helper = $helper;
    }

    public function execute()
    {
        $this->helper->setHexCodeProduct();
        shell_exec('php bin/magento indexer:reset');
        shell_exec('php bin/magento indexer:reindex');
    }
}