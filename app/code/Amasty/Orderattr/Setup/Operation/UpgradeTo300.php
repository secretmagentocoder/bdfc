<?php

namespace Amasty\Orderattr\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo300
{
    /**
     * @var CreateEavAttributeCustomerGroupTable
     */
    private $createEavAttributeCustomerGroupTable;

    /**
     * @var CreateEavAttributeStoreTable
     */
    private $createEavAttributeStoreTable;

    /**
     * @var CreateEavAttributeTable
     */
    private $createEavAttributeTable;

    /**
     * @var CreateEavValuesTables
     */
    private $createEavValuesTables;

    /**
     * @var CreateEntityTable
     */
    private $createEntityTable;

    /**
     * @var CreateRelationDetailTable
     */
    private $createRelationDetailTable;

    /**
     * @var CreateRelationTable
     */
    private $createRelationTable;

    /**
     * @var CreateShippingMethodsTable
     */
    private $createShippingMethodsTable;

    /**
     * @var CreateAttributeTooltipTable
     */
    private $createAttributeTooltipTable;

    public function __construct(
        CreateEavAttributeCustomerGroupTable $createEavAttributeCustomerGroupTable,
        CreateEavAttributeStoreTable $createEavAttributeStoreTable,
        CreateEavAttributeTable $createEavAttributeTable,
        CreateEavValuesTables $createEavValuesTables,
        CreateEntityTable $createEntityTable,
        CreateRelationDetailTable $createRelationDetailTable,
        CreateRelationTable $createRelationTable,
        CreateShippingMethodsTable $createShippingMethodsTable,
        CreateAttributeTooltipTable $createAttributeTooltipTable
    ) {
        $this->createEavAttributeCustomerGroupTable = $createEavAttributeCustomerGroupTable;
        $this->createEavAttributeStoreTable = $createEavAttributeStoreTable;
        $this->createEavAttributeTable = $createEavAttributeTable;
        $this->createEavValuesTables = $createEavValuesTables;
        $this->createEntityTable = $createEntityTable;
        $this->createRelationDetailTable = $createRelationDetailTable;
        $this->createRelationTable = $createRelationTable;
        $this->createShippingMethodsTable = $createShippingMethodsTable;
        $this->createAttributeTooltipTable = $createAttributeTooltipTable;
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->createEntityTable->execute($setup);
        $this->createEavAttributeTable->execute($setup);
        $this->createRelationTable->execute($setup);
        $this->createRelationDetailTable->execute($setup);
        $this->createEavAttributeCustomerGroupTable->execute($setup);
        $this->createEavAttributeStoreTable->execute($setup);
        $this->createEavValuesTables->execute($setup);
        $this->createShippingMethodsTable->execute($setup);
        $this->createAttributeTooltipTable->execute($setup);
    }
}
