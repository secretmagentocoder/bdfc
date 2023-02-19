<?php

namespace Ecommage\CustomExcise\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CalExcisePrice implements ObserverInterface
{

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Observer $observer
     * @return bool|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        $priceNew = $product->getData('price');
        $productId = $observer->getProduct()->getId();
        $storeArrivalId = 2;

        if ($product->getStoreId() == 2 && $product->getTypeId() != 'virtual') {
            $dutyCheckbox = $product->getOrigData('excise_duty');

            $product = $this->productFactory->create()->setStoreId($storeArrivalId)->load($productId);
            if ($dutyCheckbox != null && ($dutyCheckbox != $product->getData('excise_duty'))) {

                if ($product->getOrigData('excise_duty') == 1) {
                    // $product->setPrice($priceNew * 2);
                    // $product->setExciseDutyPrice($priceNew * 2);
                    
                } else {
                    // $product->setPrice($priceNew / 2);
                    // $product->setExciseDutyPrice($priceNew / 2);
                }
                $product->save();
                return true;
            }
        } else if (in_array(2, array_values($product->getWebsiteIds())) && $product->getData('excise_duty') == 1 && $product->getTypeId() != 'virtual') {
            $product = $this->productRepository->getById($productId, true, $storeArrivalId);
            $product->setPrice($product->getPrice() * 2);
            // $product->setExciseDutyPrice($product->getPrice());
            $product->save();
        }
    }
}
