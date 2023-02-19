<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\OptionBase\Model\Schema;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\Declaration\ReaderComposite;

class SortTableColumnsHandler
{
    /**
     * @var ReaderComposite
     */
    private $readerComposite;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * SortTableColumns constructor.
     *
     * @param ReaderComposite $readerComposite
     * @param ResourceConnection $resource
     */
    public function __construct(
        ReaderComposite $readerComposite,
        ResourceConnection $resource
    ) {
        $this->readerComposite = $readerComposite;
        $this->resource        = $resource;
    }

    /**
     * @param string $moduleName
     * @param array $tableData
     */
    public function sortTableColumnProcess(string $moduleName, array $tableData)
    {
        $data       = $this->readerComposite->read($moduleName);
        $installer  = $this->resource;
        $connection = $installer->getConnection();

        foreach ($tableData as $tableName) {
            $dbTableName = $installer->getTableName($tableName);

            if ($connection->isTableExists($dbTableName)) {
                $originalColumnsState = $data['table'][$tableName]['column'];

                foreach ($originalColumnsState as $columnName => $columnSchema) {
                    if (isset($columnSchema['disabled']) && $columnSchema['disabled']) {
                        unset($originalColumnsState[$columnName]);
                    }
                }

                $originalColumnsStateMap     = array_keys($originalColumnsState);
                $originalColumnsStateReverse = array_reverse($originalColumnsStateMap, true);
                $currentColumnsState         = $this->describeTableColumnProcess(
                    $dbTableName,
                    $originalColumnsState,
                    $connection
                );

                if ($this->schemaValidation($currentColumnsState, $originalColumnsStateMap)) {
                    break;
                }

                for ($i = count($originalColumnsStateReverse) - 1; $i > 0; $i--) {
                    $columnName          = $originalColumnsStateReverse[$i];
                    $columnToMove        = $columnName;
                    $definition          = $currentColumnsState[$columnName];
                    $definition['AFTER'] = $originalColumnsStateReverse[$i - 1];
                    $connection->modifyColumnByDdl($dbTableName, $columnToMove, $definition);

                    if ($i == 1) {
                        $currentColumnsState = $this->describeTableColumnProcess(
                            $dbTableName,
                            $originalColumnsState,
                            $connection
                        );
                        if ($this->schemaValidation($currentColumnsState, $originalColumnsStateMap)) {
                            break;
                        }
                        $i = count($originalColumnsState);
                    }
                }
            }
        }
    }

    /**
     * @param string $tableName
     * @param array $originalColumnsState
     * @param \Magento\Framework\DB\Adapter\Pdo\Mysql $connection
     * @return array
     */
    private function describeTableColumnProcess(
        string $tableName,
        array $originalColumnsState,
        \Magento\Framework\DB\Adapter\Pdo\Mysql $connection
    ): array {
        $tableColumnsDescribeData = $connection->describeTable($tableName);

        foreach ($tableColumnsDescribeData as $columnName => &$columnSchema) {
            if (isset($originalColumnsState[$columnName]['comment'])) {
                $columnSchema['COMMENT'] = $originalColumnsState[$columnName]['comment'];
            }
            if (isset($originalColumnsState[$columnName]['length'])) {
                $columnSchema['LENGTH'] = $originalColumnsState[$columnName]['length'];
            }
        }

        return $tableColumnsDescribeData;
    }

    /**
     * @param array $currentColumnsState
     * @param array $originalColumnsStateMap
     * @return bool
     */
    private function schemaValidation(array $currentColumnsState, array $originalColumnsStateMap): bool
    {
        return array_keys($currentColumnsState) === $originalColumnsStateMap;
    }
}
