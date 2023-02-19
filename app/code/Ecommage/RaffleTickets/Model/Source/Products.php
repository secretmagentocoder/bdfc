<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Model\Source;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Products implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * Constructors.
     *
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(CollectionFactory $productCollectionFactory)
    {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Function toOptionArray
     */
    public function toOptionArray()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
                   ->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL)
                   ->addAttributeToFilter('is_check_raffle', '1');
        $options = [];
        foreach ($collection as $product) {
            $options[] = ['label' => $product->getName(), 'value' => $product->getId()];
        }
        return $options;
    }
}
