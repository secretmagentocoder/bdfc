<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Setup\Patch\Schema;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use \MageWorx\OptionBase\Helper\Data as HelperBase;
use \MageWorx\OptionBase\Model\ProductAttributes;

class InstallForeignKey implements DataPatchInterface
{
    CONST TABLE_NAME_CATALOG_PRODUCT_ENTITY = 'catalog_product_entity';

    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * @var HelperBase
     */
    private $helperBase;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * ForeignKeyInstallProcess constructor.
     *
     * @param SchemaSetupInterface $schemaSetup
     * @param HelperBase $helperBase
     * @param ResourceConnection $resource
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup,
        HelperBase $helperBase,
        ResourceConnection $resource
    ) {
        $this->schemaSetup = $schemaSetup;
        $this->helperBase  = $helperBase;
        $this->resource    = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $installer   = $this->resource;
        $connection  = $this->schemaSetup->getConnection();
        $foreignKeys = $this->getForeignKeys();
        foreach ($foreignKeys as $item) {
            $tableName = $installer->getTableName($item['table_name']);
            if ($connection->isTableExists($tableName) &&
                !$this->helperBase->isForeignKeyExist($item, $tableName)
            ) {
                $referenceTableName = $installer->getTableName($item['reference_table_name']);

                $fkk = $installer->getFkName(
                    $tableName,
                    $item['column_name'],
                    $referenceTableName,
                    $item['reference_column_name']
                );
                $connection->addForeignKey(
                    $fkk,
                    $tableName,
                    $item['column_name'],
                    $referenceTableName,
                    $item['reference_column_name'],
                    $item['on_delete']
                );
            }
        }
    }

    /**
     * Retrieve module foreign keys data array
     *
     * @return array
     */
    public function getForeignKeys()
    {
        $dataArray = [
            [
                'table_name'            => ProductAttributes::TABLE_NAME,
                'column_name'           => ProductAttributes::COLUMN_PRODUCT_ID,
                'reference_table_name'  => static::TABLE_NAME_CATALOG_PRODUCT_ENTITY,
                'reference_column_name' => $this->helperBase->getLinkField(),
                'on_delete'             => Table::ACTION_CASCADE
            ]
        ];

        return $dataArray;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
