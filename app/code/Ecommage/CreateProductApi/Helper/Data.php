<?php

namespace Ecommage\CreateProductApi\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Psr\Log\LoggerInterface;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class Data extends AbstractHelper
{
    protected $options = [];

    protected $oldSku = '';

    protected $attributeData = [];

    private $navConfigProvider;

    public function __construct
    (
        \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory $collectionSwatch,
        Context $context,
        \Bss\Simpledetailconfigurable\Model\ResourceModel\PreselectKey\CollectionFactory $collectionPreselectKey,
        \Bss\Simpledetailconfigurable\Model\ResourceModel\PreselectKey $preselectKey,
        \Magento\Eav\Model\Config $eavConfig,
        Repository $productAttributeRepository,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory = null,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableType,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        LinkManagementInterface $linkManagement,
        LoggerInterface $logger,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Helper\Product\Options\Factory $optionsFactory,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $repository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Ecommage\CheckoutData\Helper\Data $data,
        ConfigProvider $navConfigProvider
    )
    {
        $this->collectionSwatch = $collectionSwatch;
        $this->collectionPreselectKey = $collectionPreselectKey;
        $this->preselectKey = $preselectKey;
        $this->eavConfig = $eavConfig;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->attributeFactory = $attributeFactory ?: \Magento\Framework\App\ObjectManager::getInstance();
        $this->configurableType = $configurableType;
        $this->collectionFactory = $collectionFactory;
        $this->linkManagement = $linkManagement;
        $this->_logger = $logger;
        $this->productFactory = $productFactory;
        $this->_optionsFactory = $optionsFactory;
        $this->repositoryAttribute = $repository;
        $this->productRepository = $productRepository;
        $this->helper = $data;
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function getData()
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $url = $host.'/Company(%27'.$company.'%27)/WebAttributeValueMapping?$format=application/json&$filter=Master_Product%20ne%20%27%27';
        return $this->helper->curlCall($url); 
    }

    public function setVariant()
    {
        $this->setAttributeValueProduct();
        if ($this->options == [])
        {
            $this->fitterArray();
        }
        $a = [];
        foreach ($this->getData() as $item)
        {
            try {
                if (!empty($item['Attribute_is_Variant']) && $item['Item_No'] == $item['Master_Product'] &&
                    empty($this->isExitProduct($item['Master_Product'].'-master')))
                {
                    $product  = $this->productRepository->get($item['Master_Product']);
                    $this->oldSku = $product->getSku();
                    $product->setSku($product->getSku().'-master');
                    $product->setTypeId('configurable');
                    $product->setStockData([
                                               'use_config_manage_stock' => 1,
                                               'qty' => 100,
                                               'is_qty_decimal' => 0,
                                               'is_in_stock' => 1,
                                           ]);
                    $product->setUrlKey($product->getUrlKey().'-master');
                    $this->productRepository->save($product);
                    $this->duplicateProduct($product);
                }
            }catch (\Exception $e)
            {
                $a[] = [ $item['Item_No'] => $e->getMessage() ];
                $this->_logger->error($e->getMessage());
                continue;
            }

        }
        $this->getMasterProduct();
        $this->setAttributeValueProduct();
        $this->mappingValueProduct();
        $this->setSdcp();
        $this->setVisibilityProduct();
    }



    protected function mappingValueProduct()
    {
        $childrenIds= [];
        if ($this->options == [])
        {
            $this->fitterArray();
        }
        foreach ($this->options as $key => $option) {
            try {
                if (!empty($this->isExitProduct($key . '-master'))) {
                    $key = $key . '-master';
                }
                $keys = array_intersect(array_column($option, 'Attribute_is_Variant'),[false]);
                $option = $this->unsetOptions($keys,$option);
                $optionAttribute = array_unique(array_column($option, 'Web_Attribute_Name'));
                $itemNo          = array_unique(array_column($option, 'Item_No'));
                $product         = $this->productRepository->get($key);
                if ($product->getTypeId() == 'configurable'){
                    $childrenIds = $product->getTypeInstance()->getUsedProductIds($product);
                    $newPro = array_diff($this->getProductBySku($itemNo), $childrenIds);
                    $arrUnset = array_diff($childrenIds,$this->getProductBySku($itemNo));
                    $childrenIds = $this->unsetChildrenId($arrUnset,$childrenIds);
                    if (!empty($newPro)) {
                        $childrenIds = array_merge($childrenIds, $newPro);
                        $product->getExtensionAttributes()->setConfigurableProductOptions($this->setOptionData($optionAttribute));
                        $product->getExtensionAttributes()->setConfigurableProductLinks($childrenIds);
                        $this->productRepository->save($product);
                    }
                }
            }catch (\Exception $e)
            {
                $this->_logger->error($e->getMessage());
                continue;
            }
        }
        return $this;
    }

    protected function setVisibilityProduct()
    {
        foreach ($this->getData() as $item)
        {
            try {
                $this->productRepository->get($item['Item_No'])
                                        ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH)
                                        ->save();
            }catch (\Exception $e)
            {
                $this->_logger->error($e->getMessage());
                continue;
            }
        }
        return $this;
    }

    protected function unsetOptions($arr,$option)
    {
        if (!empty($arr))
        {
            foreach ($arr as $key => $item)
            {
                unset($option[$key]);
            }
        }
        return $option;
    }

    protected function unsetChildrenId($data,$childrenIds)
    {
        if (!empty($data))
        {
            foreach ($data as $datum)
            {
                $key = array_search($datum, $childrenIds);
                if (false !== $key) {
                    unset($childrenIds[$key]);
                }
            }

        }
        return $childrenIds;
    }

    public function getProductBySku($sku,$operator = 'in')
    {
        $arr = [];
        $collection =  $this->collectionFactory->create()
                                               ->addFieldToFilter('sku',[$operator => $sku]);

        foreach ($collection as $product)
        {
            $arr[] = $product->getId();
        }

        return $arr;
    }

    public function setAttributeValueProduct()
    {
        $data = $this->getData();
        foreach ($data as $datum)
        {
            try {
                if (!empty($this->isExitProduct($datum['Item_No'])))
                {
                    $product = $this->productFactory->create()->loadByAttribute('sku',$datum['Item_No']);
                    $product->setCustomAttribute(strtolower($datum['Web_Attribute_Name']),$this->getOptionAttribute(strtolower($datum['Web_Attribute_Name']),$datum['Web_Attribute_Value']));
                    $product->save();
                }
            }catch (\Exception $e)
            {
                $this->_logger->error($e->getMessage());
                continue;
            }

        }
        return $this;
    }

    public function setSdcp()
    {
        try {
            $arrProduct = $this->getProductBySku('%-master%','like');
            foreach ($arrProduct as $id)
            {
                $product = $this->productRepository->getById($id);
                $attributeOption = $this->getAttributeByProduct(chop($product->getSku(),'-master'));
                $value = $this->collectionPreselectKey->create()->getArrayData($id);
                foreach ($attributeOption as $key => $attribute)
                {
                    $att = $this->getAttributeId($key);
                    if (array_key_exists($att->getAttributeId(),$value) && $value[$att->getAttributeId()] == $attribute) continue;
                    $this->preselectKey->savePreselectKey($product->getId(),$att->getAttributeId(),$attribute);
                }
            }
        }catch (\Exception $e)
        {
            $this->_logger->error($e->getMessage());
        }


        return $this;
    }

    protected function getAttributeId($code)
    {
        return $this->eavConfig->getAttribute('catalog_product', $code);
    }

    protected function setOptionData($optionAttribute)
    {
        if (empty($this->attributeData))
        {
            $this->setAttribute($optionAttribute);
        }

        return $this->_optionsFactory->create($this->getConfigurableAttributesData($this->attributeData));
    }

    protected function getOptionAttribute($attributeCode,$value)
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        foreach ($options as $option)
        {
            if (strtolower($option['label']) == strtolower($value))
            {
                return $option['value'];
            }
        }

        return [];
    }

    protected function isExitProduct($sku)
    {
        return $this->collectionFactory->create()
                                       ->addFieldToFilter('sku',$sku)->getSize();
    }

    protected function setAttribute($optionAttribute)
    {
        $i = 0;
        foreach ($optionAttribute as $item)
        {
            $attribute = $this->productAttributeRepository->get(strtolower($item));
            $this->attributeData[$attribute->getAttributeId()] = [ 'position' => $i++] ;
        }

        return $this;
    }


    /**
     * Get Configurable Attribute Data
     *
     * @param int[] $attributeData
     * @return array
     */
    private function getConfigurableAttributesData($attributeData)
    {
        $configurableAttributesData = [];
        $attributeValues = [];
        $attributes = $this->attributeFactory->create()
                                             ->getCollection()
                                             ->addFieldToFilter('attribute_id', array_keys($attributeData))
                                             ->getItems();
        foreach ($attributes as $attribute) {
            foreach ($attribute->getOptions() as $option) {
                if ($option->getValue()) {
                    $attributeValues[] = [
                        'label' => $option->getLabel(),
                        'attribute_id' => $attribute->getId(),
                        'value_index' => $option->getValue(),
                    ];
                }
            }
            $configurableAttributesData[] =
                [
                    'attribute_id' => $attribute->getId(),
                    'code' => $attribute->getAttributeCode(),
                    'label' => $attribute->getStoreLabel(),
                    'position' => $attributeData[$attribute->getId()]['position'],
                    'values' => $attributeValues,
                ];
        }

        return $configurableAttributesData;
    }


    public function fitterArray()
    {
        if ($this->getData())
        {
            foreach ($this->getData() as $data)
            {
                $this->options[$data['Master_Product']][] = $data;
            }
        }
        return $this;
    }

    public function duplicateProduct($product)
    {
        /** @var $product Product */
        try {
            $product = $this->productFactory->create()->loadByAttribute('sku',$product->getSku());
            $newProduct = $this->productFactory->create();
            $newProduct ->setData($product->getData());
            $newProduct->setSku($this->oldSku);
            $newProduct->setTypeId('simple');
            $newProduct->setUrlKey(null);
            $newProduct->setId(null);
            $newProduct->setWebsiteIds($product->getWebsiteIds());
            $newProduct->setStockData(
                array(
                    'use_config_manage_stock' => 1,
                    'manage_stock' => 1,
                    'is_in_stock' => 10,
                    'qty' => $product->getQty()
                )
            );
            $newProduct->save();
            return $newProduct;
        }catch (\Exception $e)
        {
            $this->_logger->error($e->getMessage());
        }
    }

    public function getMasterProduct()
    {
        $collection = $this->getProductBySku('%-master%','like');
        foreach ($collection as $productId)
        {
            try {
                $product = $this->productRepository->getById($productId);
                if ($product->getTypeId() == 'configurable')
                {
                    $this->oldSku = trim(str_replace('-master','',$product->getSku()));
                    if (empty($this->isExitProduct($this->oldSku))) {
                        $this->duplicateProduct($product);
                    }
                    
                }
            }catch (\Exception $e)
            {
                $this->_logger->error($e->getMessage());
                continue;
            }
        }
        return $this;
    }

    public function getAttributeByProduct($parentSku)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $url = $host.'/Company('.'\''.$company.'\''.')/WebAttributeValueMapping?$format=application/json&$filter=Item_No%20eq%20%27' . $parentSku . '%27';
        $data = $this->helper->curlCall($url);
        $arr = [];
        foreach ($data as $items)
        {
            $arr[strtolower($items['Web_Attribute_Name'])] = $this->getOptionAttribute(strtolower($items['Web_Attribute_Name']),strtolower($items['Web_Attribute_Value']));
        }

        return $arr;
    }

    public function getResponseApi()
    {
        $url = $host.'/Company('.'\''.$company.'\''.')/WebAttributeValueMapping?$format=application/json';
        $data = $this->helper->curlCall($url);
        return $data ;
    }

    protected function setValueHexCode()
    {
        $data = $this->getResponseApi();
        $arr = [];
        if (!empty($data))
        {
            foreach ($data as $item)
            {
                if (strtolower($item['Web_Attribute_Name']) == strtolower('color')
                    && !empty($item['Hex_Code'] && !array_key_exists($item['Hex_Code'],$arr)))
                {
                    $arr[$item['Hex_Code']] = [
                        'value' => $item['Hex_Code'],
                        'label' => $item['Web_Attribute_Value']
                    ];
                }
            }
        }

        return $arr;
    }

    public function setHexCodeProduct()
    {
        $attribute = $this->attributeFactory->create()->loadByCode(Product::ENTITY,'color');
        $options = $attribute->getSource()->getAllOptions();
        foreach ($options as $option)
        {
            try {
                    $hexColor = $this->getValueColor($option['label']);
                    if (!empty($hexColor) && is_array($option) && !empty($option['value']))
                    {
                        $this->getSwatchCollection($option['value'])->setValue($hexColor)->save();
                    }

            }catch (\Exception $e)
            {
               $this->_logger($e->getMessage());
               continue;
            }
        }

    }

    protected function getValueColor($label)
    {
        $option = $this->setValueHexCode();
        foreach ($option as $item)
        {
            if (strtolower($item['label']) == strtolower($label))
            {
                return $item['value'];
            }
        }
        return [];
    }

    public function getSwatchCollection($optionId)
    {
        return $this->collectionSwatch->create()
                                      ->addFieldtoFilter('option_id',$optionId)
                                      ->getFirstItem();
    }

}
