<?php
/**
 * Webkuls Software.
 *
 * @category  Webkuls
 * @package   Webkuls_SpecialPromotions
 * @author    Webkuls
 * @copyright Copyright (c) Webkuls Software Private Limited (https://Webkuls.com)
 * @license   https://store.Webkuls.com/license.html
 */
namespace Webkuls\SpecialPromotions\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Upgrade the SalesRule module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * add columns to salerule
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();
        $connection->addColumn(
            $setup->getTable('salesrule'),
            'wkrulesrule',
            [
                'type' => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Sales rules',
            ]
        );
        $connection->addColumn(
            $setup->getTable('salesrule'),
            'wkrulesrule_nqty',
            [
                'type' => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'wk sales rules QTY',
            ]
        );
        $connection->addColumn(
            $setup->getTable('salesrule'),
            'wkrulesrule_skip_rule',
            [
                'type' => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Sales skip rules',
            ]
        );
        $connection->addColumn(
            $setup->getTable('salesrule'),
            'max_discount',
            [
                'type' => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Maximum Discount',
            ]
        );
        $connection->addColumn(
            $setup->getTable('salesrule'),
            'promo_cats',
            [
                'type' => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'PromotionCategories',
            ]
        );
        $connection->addColumn(
            $setup->getTable('salesrule'),
            'promo_skus',
            [
                'type' => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Promotion SKUs',
            ]
        );
        $connection->addColumn(
            $setup->getTable('salesrule'),
            'n_threshold',
            [
                'type' => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'after n product discount',
            ]
        );
        $setup->endSetup();
    }
}
