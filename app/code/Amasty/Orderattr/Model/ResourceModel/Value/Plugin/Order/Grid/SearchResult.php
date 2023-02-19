<?php

namespace Amasty\Orderattr\Model\ResourceModel\Value\Plugin\Order\Grid;

use Amasty\Orderattr\Api\Data\CheckoutEntityInterface;
use Amasty\Orderattr\Model\ConfigProvider;
use Amasty\Orderattr\Model\ResourceModel\Entity\Entity;
use Magento\Framework\App\ResourceConnection;

class SearchResult
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var array
     */
    protected $columns = [];

    public function __construct(
        ConfigProvider $configProvider,
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
        $this->configProvider = $configProvider;
        $this->flatTable = $this->resource->getTableName(Entity::GRID_INDEXER_ID . '_flat');
    }

    public function afterGetSelect(
        \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult $collection,
        $select
    ) {

        if ($collection->getResource() instanceof \Magento\Sales\Model\ResourceModel\Order) {

            return $this->addColumnsToGrid($select, 'entity_id');
        } elseif ($collection->getResource() instanceof \Magento\Sales\Model\ResourceModel\Order\Invoice) {
            if ($this->configProvider->isShowInvoiceGrid()) {

                return $this->addColumnsToGrid($select, 'order_id');
            }
        } elseif ($collection->getResource() instanceof \Magento\Sales\Model\ResourceModel\Order\Shipment) {
            if ($this->configProvider->isShowShipmentGrid()) {

                return $this->addColumnsToGrid($select, 'order_id');
            }
        }

        return $select;
    }

    protected function addColumnsToGrid($select, $orderField)
    {
        if ((string)$select == "") {
            return $select;
        }

        if (!$this->columns) {
            $connection = $this->resource->getConnection();
            $fields = $connection->describeTable($this->flatTable);
            unset($fields['parent_id']);
            unset($fields['entity_id']);
            foreach ($fields as $field => $value) {
                $this->columns[] = 'amorderattr.' . $field;
            }
        }

        if (!array_key_exists('amorderattr', $select->getPart('from')) && strpos($select, 'COUNT') === false) {
            $select->joinLeft(
                ['amorderattr' => $this->flatTable],
                'main_table.' . $orderField . ' = amorderattr.' . CheckoutEntityInterface::PARENT_ID,
                $this->columns
            );
        }

        return $select;
    }
}
