<?php

namespace Bodak\CheckoutCustomForm\Plugin\Checkout\CustomerData;

use Magento\Framework\App\ObjectManager;

class Cart
{

    protected $option = [];
    public function __construct
    (
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory $collectionFactory,
        \Magento\Eav\Model\Entity\Attribute $eavConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\View\LayoutInterface $layout
    )
    {
        $this->json = $json;
        $this->collectionFactory =$collectionFactory;
        $this->eavConfig = $eavConfig;
        $this->productRepository = $productRepository;
        $this->layout = $layout;

    }

    public function afterGetItemData(\Magento\Checkout\CustomerData\AbstractItem $subject, array $result)
    {
         $valueAttribute = $this->getOptionLabelByValue($result['item_id'],$result['product_id']);
         $option = [];
         if (!empty($valueAttribute[0]) && array_key_exists('value',$valueAttribute[0])){
             $option = $this->json->unserialize($valueAttribute[0]['value']);
         }

         $product = $this->getProduct($result['product_id']);
         if ( !empty($option) && array_key_exists('options',$result)){
             $result['options'] = $this->setOptions($result['options'],$option);
         }

         if (empty($product->getIsCheckRaffle()))
         {
             $result['price_sale'] = $this->getHtmlPrice($result['product_id']);
         }

        return $result;
    }

    public function getHtmlPrice($id)
    {
        $product = $this->getProduct($id);
        $priceRender = $this->layout->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->layout->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'display_minimal_price'  => true,
                    'use_link_for_as_low_as' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            );
        }

        return $price;
    }

    public function getProduct($id)
    {
        return $this->productRepository->getById($id);
    }


        public function getOptionLabelByValue($itemId,$productId)
    {
       return $this->collectionFactory->create()
                                      ->addFieldToFilter('code','attributes')
                                      ->addFieldToFilter('product_id',$productId)
                                      ->addFieldToFilter('item_id',$itemId)->getData();
    }

    protected function setOptions($options,$data)
    {
        $item = [];
        foreach ($options as $option)
        {
            $attribute = $this->eavConfig->load($option['option_id'])->getOptions();
            $arr = $this->getOptionAll($attribute);

            if (array_key_exists($option['option_id'],$data))
            {
                $item[] = [
                  'label' => $option['label'],
                  "value" => $arr[$data[$option['option_id']]]['label'],
                  "option_id" => $option['option_id'],
                  "option_value" => $option['option_value']
                ];
            }
        }
       return $item;
    }

    protected function getOptionAll($attribute)
    {
        foreach ($attribute as $item)
        {
            $this->option[$item->getValue()] = [
                'value' =>  $item->getValue(),
                'label' => $item->getLabel()
          ];
        }
        return $this->option;
    }
}
