<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Model;

use Bss\Simpledetailconfigurable\Api\Data\AdditionalInfoInterface;
use Bss\Simpledetailconfigurable\Api\Data\AdditionalInfoInterfaceFactory;
use Bss\Simpledetailconfigurable\Api\Data\AttributesSelectInterface;
use Bss\Simpledetailconfigurable\Api\Data\ChildProductDataInterface;
use Bss\Simpledetailconfigurable\Api\Data\ChildProductDataInterfaceFactory;
use Bss\Simpledetailconfigurable\Api\Data\ConfigurationInterface;
use Bss\Simpledetailconfigurable\Api\Data\ConfigurationInterfaceFactory;
use Bss\Simpledetailconfigurable\Api\Data\ImageDataInterface;
use Bss\Simpledetailconfigurable\Api\Data\ImageDataInterfaceFactory;
use Bss\Simpledetailconfigurable\Api\Data\MetaDataInterface;
use Bss\Simpledetailconfigurable\Api\Data\MetaDataInterfaceFactory;
use Bss\Simpledetailconfigurable\Api\Data\GeneralConfigInterface;
use Bss\Simpledetailconfigurable\Api\Data\GeneralConfigInterfaceFactory;
use Bss\Simpledetailconfigurable\Api\Data\PreselectDataInterface;
use Bss\Simpledetailconfigurable\Api\Data\PreselectDataInterfaceFactory;
use Bss\Simpledetailconfigurable\Api\Data\ProductDataInterface;
use Bss\Simpledetailconfigurable\Api\Data\ProductDataInterfaceFactory;
use Bss\Simpledetailconfigurable\Api\Data\StockDataInterface;
use Bss\Simpledetailconfigurable\Api\Data\StockDataInterfaceFactory;
use Bss\Simpledetailconfigurable\Api\Data\TierPriceInterface;
use Bss\Simpledetailconfigurable\Api\Data\TierPriceInterfaceFactory;
use Bss\Simpledetailconfigurable\Api\Data\ReviewInterface;
use Bss\Simpledetailconfigurable\Api\Data\ReviewInterfaceFactory;
use Bss\Simpledetailconfigurable\Api\SimpleDetailManagementInterface;
use Bss\Simpledetailconfigurable\Helper\ProductData;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;

class SimpleDetailManagement implements SimpleDetailManagementInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ConfigurationInterfaceFactory
     */
    protected $configurationFactory;

    /**
     * @var ProductDataInterfaceFactory
     */
    protected $productDataFactory;

    /**
     * @var ChildProductDataInterfaceFactory
     */
    protected $childProductDataFactory;

    /**
     * @var ImageDataInterfaceFactory
     */
    protected $imageDataFactory;

    /**
     * @var StockDataInterfaceFactory
     */
    protected $stockDataFactory;

    /**
     * @var AdditionalInfoInterfaceFactory
     */
    protected $additionalInfoFactory;

    /**
     * @var MetaDataInterfaceFactory
     */
    protected $metaDataFactory;

    /**
     * @var GeneralConfigInterfaceFactory
     */
    protected $generalConfigFactory;

    /**
     * @var ReviewInterfaceFactory
     */
    protected $reviewFactory;

    /**
     * @var PreselectDataInterfaceFactory
     */
    protected $preselectDataFactory;

    /**
     * @var TierPriceInterfaceFactory
     */
    protected $tierPriceFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ProductData
     */
    protected $productData;

    /**
     * @var ReviewCollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * SimpleDetailManagement constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ConfigurationInterfaceFactory $configurationFactory
     * @param ProductDataInterfaceFactory $productDataFactory
     * @param ChildProductDataInterfaceFactory $childProductDataFactory
     * @param ImageDataInterfaceFactory $imageDataFactory
     * @param StockDataInterfaceFactory $stockDataFactory
     * @param AdditionalInfoInterfaceFactory $additionalInfoFactory
     * @param MetaDataInterfaceFactory $metaDataFactory
     * @param GeneralConfigInterfaceFactory $generalConfigFactory
     * @param ReviewInterfaceFactory $reviewFactory
     * @param PreselectDataInterfaceFactory $preselectDataFactory
     * @param TierPriceInterfaceFactory $tierPriceFactory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ProductRepositoryInterface $productRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductData $productData
     * @param ReviewCollectionFactory $reviewCollectionFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ConfigurationInterfaceFactory $configurationFactory,
        ProductDataInterfaceFactory $productDataFactory,
        ChildProductDataInterfaceFactory $childProductDataFactory,
        ImageDataInterfaceFactory $imageDataFactory,
        StockDataInterfaceFactory $stockDataFactory,
        AdditionalInfoInterfaceFactory $additionalInfoFactory,
        MetaDataInterfaceFactory $metaDataFactory,
        GeneralConfigInterfaceFactory $generalConfigFactory,
        ReviewInterfaceFactory $reviewFactory,
        PreselectDataInterfaceFactory $preselectDataFactory,
        TierPriceInterfaceFactory $tierPriceFactory,
        AttributeRepositoryInterface $attributeRepository,
        ProductRepositoryInterface $productRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductData $productData,
        ReviewCollectionFactory $reviewCollectionFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->configurationFactory = $configurationFactory;
        $this->productDataFactory = $productDataFactory;
        $this->childProductDataFactory = $childProductDataFactory;
        $this->imageDataFactory = $imageDataFactory;
        $this->stockDataFactory = $stockDataFactory;
        $this->additionalInfoFactory = $additionalInfoFactory;
        $this->metaDataFactory = $metaDataFactory;
        $this->generalConfigFactory = $generalConfigFactory;
        $this->reviewFactory = $reviewFactory;
        $this->preselectDataFactory = $preselectDataFactory;
        $this->tierPriceFactory = $tierPriceFactory;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productData = $productData;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration($storeId)
    {
        $mapConfig = ConfigurationInterface::MAP;
        $store = $this->storeManager->getStore($storeId);
        /** @var ConfigurationInterface $configuration */
        $configuration = $this->configurationFactory->create();
        foreach ($mapConfig as $setterFunc => $path) {
            $configuration->$setterFunc($this->scopeConfig->getValue(
                $path,
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            ));
        }
        return $configuration;
    }

    /**
     * @inheritDoc
     */
    public function getProductData($sku, $storeId)
    {
        $product = $this->productRepository->get($sku);
        $store = $this->storeManager->getStore($storeId);

        if ($product->getTypeId() !== 'configurable') {
            throw new InputException(__('We does not support this product type: %1', $product->getTypeId()));
        }
        $rawProductData = $this->productData->getAllData($product->getId());

        /** @var ProductDataInterface $productData */
        $productData = $this->productDataFactory->create();
        $productData->setSku($product->getSku());
        $productData->setName($product->getName());

        $productData->setUrl($rawProductData['url'] ?? '');

        $items = $rawProductData['child'] ?? [];
        $additionalInfo = $rawProductData['additional_info'] ?? [];
        $preselect = $rawProductData['preselect'] ?? [];
        $images = $rawProductData['image'] ?? [];
        $metaData = $rawProductData['meta_data'] ?? [];

        $stockData = $this->setStockData(
            (bool)$rawProductData['stock_status'] ?? false,
            (float)$rawProductData['stock_number'] ?? 0
        );
        $productData->setStockData($stockData);

        $metaDataObj = $this->setMetaData(
            $metaData['meta_description'] ?? '',
            $metaData['meta_keyword'] ?? '',
            $metaData['meta_title'] ?? ''
        );
        $productData->setMetaData($metaDataObj);

        $preselectData = $this->setPreselect($preselect);
        $productData->setPreselect($preselectData);

        /** @var GeneralConfigInterface $generalConfig */
        $generalConfig = $this->generalConfigFactory->create();
        $generalConfigData = $this->productData->getEnabledModuleOnProduct($product->getId())->getData();
        $generalConfig->setEnableAjaxOnProduct($generalConfigData['is_ajax_load'] ?? true);
        $generalConfig->setEnableModuleOnProduct($generalConfigData['enabled'] ?? true);
        $productData->setGeneralConfig($generalConfig);

        $productData->setPrice(0);

        $imagesData = $this->setImages($images);
        $productData->setImages($imagesData);

        $productData->setDesc($rawProductData['desc'] ?? '');

        $childData = $this->setChildData($items, $store->getId());
        $productData->setItems($childData);

        $additionalInfoData = $this->setAdditionalInfo($additionalInfo);
        $productData->setAdditionalInfo($additionalInfoData);

        return $productData;
    }

    /**
     * @param bool $stockStatus
     * @param float $stockQty
     * @return StockDataInterface
     */
    protected function setStockData($stockStatus, $stockQty)
    {
        /** @var StockDataInterface $stockData */
        $stockData = $this->stockDataFactory->create();
        $stockData->setIsInStock($stockStatus);
        $stockData->setSalableQty($stockQty);
        return $stockData;
    }

    /**
     * @param string $description
     * @param string $keyword
     * @param string $title
     * @return MetaDataInterface
     */
    protected function setMetaData($description, $keyword, $title)
    {
        /** @var MetaDataInterface $metaData */
        $metaData = $this->metaDataFactory->create();
        $metaData->setMetaDescription($description);
        $metaData->setMetaKeyword($keyword);
        $metaData->setMetaTitle($title);
        return $metaData;
    }

    /**
     * @param array $preselect
     * @return array
     */
    protected function setPreselect($preselect)
    {
        $dataSelected = $preselect['data'] ?? [];
        $preselectArr = [];
        foreach ($dataSelected as $attrId => $attrValue) {
            /** @var PreselectDataInterface $preselectData */
            $preselectData = $this->preselectDataFactory->create();
            $preselectData->setAttributeId((int)$attrId);
            $preselectData->setSelectedValue($attrValue);
            $preselectArr[] = $preselectData;
        }
        return $preselectArr;
    }

    /**
     * @param array $images
     * @return array
     */
    protected function setImages($images)
    {
        $imagesArr = [];
        foreach ($images as $image) {
            /** @var ImageDataInterface $imageData */
            $imageData = $this->imageDataFactory->create();
            $imageData->setVideoUrl($image['videoUrl'] ?? '');
            $imageData->setCaption($image['caption'] ?? '');
            $imageData->setFull($image['full'] ?? '');
            $imageData->setImg($image['img'] ?? '');
            $imageData->setIsMain($image['isMain'] ?? false);
            $imageData->setType($image['type'] ?? '');
            $imageData->setPosition(isset($image['position']) ? (int)$image['position'] : 0);
            $imagesArr[] = $imageData;
        }
        return $imagesArr;
    }

    /**
     * @param array $childItems
     * @param int $storeId
     * @return array
     */
    protected function setChildData($childItems, $storeId = 0)
    {
        $reviewData = $this->getReviewData(array_keys($childItems), $storeId);

        $childProductDataArr = [];
        foreach ($childItems as $childItem) {
            /** @var ChildProductDataInterface $childProductData */
            $childProductData = $this->childProductDataFactory->create();
            if (isset($childItem['entity'])) {
                $childProductData->setEntity((int)$childItem['entity']);
                $childProductData->setSku($childItem['sku']);
                $childProductData->setName($childItem['name']);
                $childProductData->setDesc($childItem['desc']);

                $childMetaDataArr = $childItem['meta_data'] ?? [];
                $childMetaData = $this->setMetaData(
                    $childMetaDataArr['meta_description'] ?? '',
                    $childMetaDataArr['meta_keyword'] ?? '',
                    $childMetaDataArr['meta_title'] ?? ''
                );
                $childProductData->setMetaData($childMetaData);

                $stockDataArr = $this->setStockData(
                    (bool)$childItem['stock_status'] ?? false,
                    (float)$childItem['stock_number'] ?? 0
                );
                $childProductData->setStockData($stockDataArr);

                $childImagesArr = $childItem['image'] ?? [];
                $childItems = $this->setImages($childImagesArr);
                $childProductData->setImages($childItems);

                $tierPrices = $this->setTierPrices($childItem['price']['tier_price'] ?? []);
                $childProductData->setTierPrices($tierPrices);

                $additionalInfo = $this->setAdditionalInfo($childItem['additional_info'] ?? []);
                $childProductData->setAdditionalInfo($additionalInfo);

                // Reviews
                $childProductData->setReviewCount($childItem['review_count'] ?? 0);
                $reviewsAvailable = array_filter($reviewData, function ($reviewArr) use ($childItem) {
                    return isset($reviewArr['entity_pk_value']) &&
                        $reviewArr['entity_pk_value'] == $childItem['entity'];
                });
                $reviews = [];
                if (!empty($reviewsAvailable)) {
                    foreach ($reviewsAvailable as $reviewItemData) {
                        /** @var ReviewInterface $reviewTemp */
                        $reviewTemp = $this->setReviewItem($reviewItemData);
                        $reviews[] = $reviewTemp;
                    }
                }
                $childProductData->setReviews($reviews);

                $childProductDataArr[] = $childProductData;
            }
        }
        return $childProductDataArr;
    }

    /**
     * @param array|int|string $products
     * @param int $storeId
     * @return array
     */
    protected function getReviewData($products, $storeId = 0)
    {
        /** @var \Magento\Review\Model\ResourceModel\Review\Collection $reviewCollection */
        $reviewCollection = $this->reviewCollectionFactory->create();
        $reviewCollection->addStoreFilter(
            $storeId
        )->addStatusFilter(
            \Magento\Review\Model\Review::STATUS_APPROVED
        );
        if (is_array($products)) {
            $reviewCollection->addFieldToFilter(
                'entity_pk_value',
                [
                    'in' => $products
                ]
            );
        } elseif (is_string($products) || is_numeric($products)) {
            $reviewCollection->addEntityFilter(
                'product',
                $products
            );
        }
        $reviewData = $reviewCollection->getData();
        return $reviewData;
    }

    /**
     * @param array $reviewItemData
     * @return ReviewInterface
     */
    protected function setReviewItem($reviewItemData)
    {
        /** @var ReviewInterface $reviewTemp */
        $reviewTemp = $this->reviewFactory->create();
        $reviewTemp->setCreatedAt($reviewItemData['created_at'] ?? '');
        $reviewTemp->setCustomerId((int)$reviewItemData['customer_id'] ?? 0);
        $reviewTemp->setDetail($reviewItemData['detail'] ?? '');
        $reviewTemp->setNickname($reviewItemData['nickname'] ?? '');
        $reviewTemp->setReviewId((int)$reviewItemData['review_id'] ?? 0);
        $reviewTemp->setStatusId((int)$reviewItemData['status_id'] ?? 0);
        $reviewTemp->setTitle($reviewItemData['title'] ?? '');
        return $reviewTemp;
    }

    /**
     * @param array $tiers
     * @return array
     */
    protected function setTierPrices($tiers)
    {
        $tierPrices = [];
        foreach ($tiers as $tier) {
            /** @var TierPriceInterface $tierPrice */
            $tierPrice = $this->tierPriceFactory->create();
            $tierPrice->setQty((float)$tier['qty'] ?? 0);
            $tierPrice->setValue((float)$tier['value'] ?? 0);
            $tierPrice->setFinal((float)$tier['final'] ?? 0);
            $tierPrice->setBase((float)$tier['base'] ?? 0);
            $tierPrice->setFinalDiscount((float)$tier['final_discount'] ?? 0);
            $tierPrice->setBaseDiscount((float)$tier['base_discount'] ?? 0);
            $tierPrice->setPercent((float)$tier['percent'] ?? 0);
            $tierPrices[] = $tierPrice;
        }
        return $tierPrices;
    }

    /**
     * @param array $infoArr
     * @return array
     */
    protected function setAdditionalInfo($infoArr)
    {
        $additionalInfoList = [];
        foreach ($infoArr as $attrCode => $info) {
            /** @var AdditionalInfoInterface $additionalInfo */
            $additionalInfo = $this->additionalInfoFactory->create();
            $additionalInfo->setCode($attrCode);
            $additionalInfo->setLabel($info['label'] ?? '');
            $additionalInfo->setValue($info['value'] ?? '');
            $additionalInfoList[] = $additionalInfo;
        }

        return $additionalInfoList;
    }

    /**
     * @inheritDoc
     */
    public function getPreselect($attributeSelect, $sku, $storeId)
    {
        $product = $this->productRepository->get($sku);

        if ($product->getTypeId() !== 'configurable') {
            throw new InputException(__('We does not support this type product: %1', $product->getTypeId()));
        }

        $store = $this->storeManager->getStore($storeId);
        $isEnableCustomUrl = $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_advanced/url',
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );

        if ($isEnableCustomUrl) {
            $attrArr = [];
            /** @var AttributesSelectInterface $attr */
            foreach ($attributeSelect as $attr) {
                $attrArr[$attr->getCode()] = $attr->getValue();
            }

            $filter = $this->filterBuilder->setField('attribute_code')
                ->setValue(array_keys($attrArr))
                ->setConditionType('in')
                ->create();
            $searchCriteria = $this->searchCriteriaBuilder->addFilters([$filter])->create();

            /** @var AttributeInterface[] $attributes */
            $attributes = $this->attributeRepository->getList(
                Product::ENTITY,
                $searchCriteria
            )->getItems();

            $supperAttributes = [];
            foreach ($attributes as $attribute) {
                if (isset($attrArr[$attribute->getAttributeCode()])) {
                    $options = $attribute->getOptions();

                    /** @var AttributeOptionInterface $option */
                    foreach ($options as $option) {
                        if ($option->getLabel() == $attrArr[$attribute->getAttributeCode()]) {
                            $supperAttributes[$attribute->getAttributeId()] = $option->getValue();
                        }
                    }
                }
            }

            if (!empty($supperAttributes)) {
                /** @var Configurable $productTypeInstance */
                $productTypeInstance = $product->getTypeInstance();
                $child = $productTypeInstance->getProductByAttributes($supperAttributes, $product);
                $childData = $this->productData->getChildDetail($child->getId());
                if (isset($childData['entity']) && $childData['entity']) {
                    $entity = $childData['entity'];
                    return $this->setChildData([$entity => $childData], $store->getId())[0] ?? [];
                }
            }
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getPreselectConfig($sku, $storeId)
    {
        $product = $this->productRepository->get($sku);
        $store = $this->storeManager->getStore($storeId);
        $preselectData = $this->productData->getSelectingData($product->getId());
        $isEnablePreselect = $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_advanced/preselect',
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );

        if ($product->getTypeId() !== 'configurable') {
            throw new InputException(__('We does not support this type product: %1', $product->getTypeId()));
        }

        if ((bool)$isEnablePreselect && !empty($preselectData)) {
            /** @var Configurable $productTypeInstance */
            $productTypeInstance = $product->getTypeInstance();
            $child = $productTypeInstance->getProductByAttributes($preselectData, $product);
            $childData = $this->productData->getChildDetail($child->getId());
            if (isset($childData['entity']) && $childData['entity']) {
                $entity = $childData['entity'];
                return $this->setChildData([$entity => $childData], $store->getId())[0] ?? [];
            }
        }
        return [];
    }
}
