<?php
/**
 * Magetop
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Magetop
 * @package    Magetop_Brand
 * @copyright  Copyright (c) 2014 Magetop (https://www.magetop.com/)
 * @license    https://www.magetop.com/LICENSE.txt
 */
namespace Magetop\Brand\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;


class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'magetop_brand_product'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magetop_brand_product')
        )->addColumn(
            'brand_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'primary' => true],
            'Brand ID'
        )->addColumn(
            'product_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Product ID'
        )->addColumn(
            'position',
            Table::TYPE_INTEGER,
            11,
            ['nullable' => true],
            'Position'
        )->setComment(
            'Magetop Brand To Product Linkage Table'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
