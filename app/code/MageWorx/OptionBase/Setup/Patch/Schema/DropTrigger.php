<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class DropTrigger implements DataPatchInterface, PatchVersionInterface
{
    const MAGEWORX_OPTION_ID                      = 'mageworx_option_id';
    const MAGEWORX_OPTION_TYPE_ID                 = 'mageworx_option_type_id';
    const CATALOG_PRODUCT_OPTION_TABLE            = 'catalog_product_option';
    const CATALOG_PRODUCT_OPTION_TYPE_VALUE_TABLE = 'catalog_product_option_type_value';

    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * DropTrigger constructor.
     *
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();
        $setup = $this->schemaSetup;

        if ($setup->getConnection()->tableColumnExists(
            $setup->getTable(static::CATALOG_PRODUCT_OPTION_TABLE),
            static::MAGEWORX_OPTION_ID
        )) {
            $triggerName = 'insert_' . static::MAGEWORX_OPTION_ID;
            $setup->getConnection()->dropTrigger($triggerName);
        }

        if ($setup->getConnection()->tableColumnExists(
            $setup->getTable(static::CATALOG_PRODUCT_OPTION_TYPE_VALUE_TABLE),
            static::MAGEWORX_OPTION_TYPE_ID
        )) {
            $triggerName = 'insert_' . static::MAGEWORX_OPTION_TYPE_ID;
            $setup->getConnection()->dropTrigger($triggerName);
        }

        $this->schemaSetup->endSetup();
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

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.4';
    }
}
