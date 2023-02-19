<?php

namespace Ecommage\CreateProductApi\Cron;

use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\App\Emulation;
use Psr\Log\LoggerInterface;

class UploadVariant
{

    const PATH = 'ecommage/variant';

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
        $this->emulation->startEnvironmentEmulation(1, Area::AREA_FRONTEND, true);
        try {
            $connection = $this->resource->getConnection();
            $select = $connection->select()
                                 ->from(
                                     'core_config_data','value'
                                 )  ->where(  'path = ?', self::PATH);
            $data = $connection->fetchOne($select);
            if (!empty($data)){
                $this->helper->setVariant();
                $this->delete();
            }
        }catch (\Exception $e)
        {
            $this->_logger->error($e->getMessage());
        }
        $this->emulation->stopEnvironmentEmulation();
        shell_exec('php bin/magento indexer:reset');
        shell_exec('php bin/magento indexer:reindex');
    }

    /**
     * @return void
     */
    public function delete()
    {

        $connection = $this->resource->getConnection();
        try {
            $myTable = $connection->getTableName('core_config_data');
            $connection->delete($myTable, ['path = ?' => self::PATH]);
            shell_exec('php bin/magento cache:clean');
        }catch (\Exception $exception)
        {
            $this->_logger->error($exception->getMessage());
        }

    }
}