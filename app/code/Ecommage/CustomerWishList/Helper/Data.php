<?php

namespace Ecommage\CustomerWishList\Helper;

use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;

/**
 *
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $categoryFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Catalog\Block\Product\AbstractProduct
     */
    protected $abstractProduct;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @var View|mixed
     */
    protected $productView;

    /**
     * @param \Magento\Catalog\Block\Product\AbstractProduct $abstractProduct
     * @param \Magento\Catalog\Block\Product\ImageBuilder    $imageBuilder
     * @param Context                                        $context
     * @param View|null                                      $productView
     */
    public function __construct
    (
        \Magento\Catalog\Model\Category $categoryFactory,
        ProductRepository $productRepository,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Serialize\Serializer\Json $json,
        AttributeRepositoryInterface $attributeRepository,
        \Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory $collectionFactory,
        \Magento\Catalog\Block\Product\AbstractProduct $abstractProduct,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        Context $context,
        ?View $productView = null
    )
    {
        $this->categoryFactory = $categoryFactory;
        $this->productRepository = $productRepository;
        $this->eavConfig = $eavConfig;
        $this->json = $json;
        $this->attributeRepository = $attributeRepository;
        $this->collectionFactory = $collectionFactory;
        $this->abstractProduct = $abstractProduct;
        $this->imageBuilder = $imageBuilder;
        $this->productView = $productView ?: ObjectManager::getInstance()->get(View::class);
        parent::__construct($context);
    }

    /**
     * Returns qty to show visually to user
     *
     * @param \Magento\Wishlist\Model\Item $item
     * @return float
     */
    public function getAddToCartQty(\Magento\Wishlist\Model\Item $item)
    {
        $qty = $item->getQty();
        $qty = $qty < $this->productView->getProductDefaultQty($this->getProductItem($item))
            ? $this->productView->getProductDefaultQty($this->getProductItem($item)) : $qty;
        return round($qty) ?: 1;
    }

    /**
     * Return product for current item
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductItem($items)
    {
        return $items->getProduct();
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->create($product, $imageId, $attributes);
    }

    /**
     * @param $product
     *
     * @return string
     */
    public function getProductPriceHtml($product)
    {
        return $this->abstractProduct->getProductPrice($product);
    }

    /**
     * @param $product
     *
     * @return mixed
     */
    public function getAttribute($product){
       return $product->getResource()->getAttribute('size')->getFrontend()->getValue($product);
    }

    /**
     * @param $product
     *
     * @return mixed
     */
    public function getTotalPrice($product)
    {
        return $product->getPriceInfo()->getPrice('final_price')->getValue();
    }

    /**
     * Retrieve URL for configuring item from wishlist
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @return string
     */
    public function getConfigureUrl($item)
    {
        if ($item){
            return $this->_getUrl(
                'wishlist/index/configure',
                [
                    'id' => $item->getWishlistItemId(),
                    'product_id' => $item->getProductId(),
                ]
            );
        }
    }

    /**
     * @param $item
     *
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOption($item)
    {
        $value = [];
        $attribute = $this->attributeRepository->get(Product::ENTITY, 'size');
        if ($item){
            $collection = $this->collectionFactory->create()
                            ->addFieldToFilter('code','attributes')
                            ->addFieldToFilter('wishlist_item_id',$item->getWishlistItemId());
            foreach ($collection as $data){
                $option = $this->json->unserialize($data->getValue());
                if (array_key_exists($attribute->getAttributeId(),$option)){
                    $value = $this->getOptionAttribute($option[$attribute->getAttributeId()]);
                }
            }
        }
        return $value;
    }

    /**
     * @param $value
     *
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOptionAttribute($value)
    {
        $option = [];
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'size')->getSource()->getAllOptions();
        foreach ($attribute as $item){
            if ($item['value'] == $value)
            {
                $option = $item['label'];
            }
        }
        return $option;
    }

    public function getProduct($product)
    {
        $name = [];
        if ($product){
            $data = $this->productRepository->getById($product->getEntityId());
            foreach ($data->getCategoryIds() as $catagoryId){
                $category = $this->categoryFactory->load($catagoryId);
                if (!empty($category) && $category->getLevel() != 1) {
                     return $category;
                }  
            }
        }
        return  $name;
    }
}