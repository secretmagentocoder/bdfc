<?php

namespace Ecommage\CheckoutCart\Helper;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory as OptionCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory as OptionValueCollectionFactory;
use Magento\Catalog\Helper\Product\Configuration;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item;

class Data extends AbstractHelper
{
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @var null
     */
    protected $connection = null;
    /**
     * @var CollectionFactory
     */
    protected $optionCollection;
    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;
    /**
     * @var Configuration
     */
    protected $productConfig;
    /**
     * @var OptionValueCollectionFactory
     */
    protected $valueCollectionFactory;

    protected $_storeManager;

    /**
     * Data constructor.
     *
     * @param Session                      $checkoutSession
     * @param Configuration                $productConfig
     * @param AttributeFactory             $attributeFactory
     * @param OptionCollectionFactory      $optionCollection
     * @param OptionValueCollectionFactory $valueCollectionFactory
     * @param Json|null                    $serializer
     * @param Context                      $context
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        Session $checkoutSession,
        Configuration $productConfig,
        AttributeFactory $attributeFactory,
        OptionCollectionFactory $optionCollection,
        OptionValueCollectionFactory $valueCollectionFactory,
        Json $serializer = null,
        Context $context
    ) {
        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;
        $this->_storeManager = $storeManager;
        $this->priceFomart            = $priceCurrency;
        $this->request                = $context->getRequest();
        $this->productConfig          = $productConfig;
        $this->checkoutSession        = $checkoutSession;
        $this->optionCollection       = $optionCollection;
        $this->attributeFactory       = $attributeFactory;
        $this->valueCollectionFactory = $valueCollectionFactory;
        $this->serializer             = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context);
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function checkRaffle()
    {
        $quote      = $this->checkoutSession->getQuote();
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $item) {
            if ($this->isRaffle($item)) {
                return true;
            }
        }
        return false;
    }

//    /**
//     * @param $option
//     *
//     * @return bool
//     */
//    public function isShowOptionRaffle($opValueId)
//    {
//        $quote      = $this->checkoutSession->getQuote();
//        $quoteItems = $quote->getAllItems();
//        /** @var Item $item */
//        foreach ($quoteItems as $item) {
//            if ($this->isRaffle($item)) {
//                $option    = $item->getOptionByCode('option_ids');
//                $optionIds = (array)$option->getValue();
//                if (strpos($option->getValue(), ',') !== false) {
//                    $optionIds = explode(',', $option->getValue());
//                }
//
//                foreach ($optionIds as $optionId) {
//                    /** @var Item\Option $optionValue */
//                    $optionValue = $item->getOptionByCode('option_' . $optionId);
//                    if ($optionValue) {
//                        $values         = $optionValue->getValue();
//                        $optionValueIds = (array)$values;
//                        if (strpos($values, ',') !== false) {
//                            $optionValueIds = explode(',', $values);
//                        }
//
//                        if (in_array($opValueId, $optionValueIds)) {
//                            return false;
//                        }
//                    }
//                }
//            }
//        }
//
//        return true;
//    }


        public function getBasePrice()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @param $option
     *
     * @return int|void
     */
    public function getPriceAmount($option)
    {
        $count = [];
        if (is_array($option)) {
            foreach ($option as $item) {
                if (array_key_exists('option_type', $item) && $item['option_type'] == 'checkbox' && array_key_exists('value', $item)) {
                    $count = explode(',', $item['value']);
                }
            }
        }
        return count($count);
    }

    /**
     * @param $item
     *
     * @return string
     */
    public function renderOptionHtml($item)
    {
        $html    = '';

        $options = $item->getProduct()
                        ->getTypeInstance(true)
                        ->getOrderOptions($item->getProduct());
        if (!empty($options)) {
            $product = $item->getProduct();
            if (isset($options['info_buyRequest']['super_attribute'])) {
                $html           = '<dl class="item-options">';
                $superAttribute = $options['info_buyRequest']['super_attribute'];
                foreach ($superAttribute as $attributeId => $attributeOptionId) {
                    $attribute           = $this->attributeFactory->create();
                    $attributeModel      = $attribute->load($attributeId);
                    $attributeCode       = $attributeModel->getAttributeCode();
                    $_attributeId        = $product->getResource()->getAttribute($attributeCode);
                    $attributeOptionText = $attributeOptionId;
                    if ($_attributeId->usesSource()) {
                        $attributeOptionText = $_attributeId->getSource()->getOptionText($attributeOptionId);
                    }

                    $html .= '<dt>' . $attributeModel->getFrontendLabel() . '</dt>';
                    if ($attributeModel->getAttributeCode() == 'color'){
                        $color = $this->getSwatchColor($attributeOptionId);
                        $html .= '<dd><div class="swatch-option color selected" id="option-label-color-93-item-'.$attributeOptionId.'" index="1" aria-checked="false" aria-describedby="option-label-color-93" tabindex="0" data-option-type="1" data-option-id="'.$attributeOptionId.'" role="option" data-thumb-width="110" data-thumb-height="90" data-option-tooltip-value="'.$color->getValue().'" style="background: '.$color->getValue().' no-repeat center; background-size: initial;"></div></dd>';
                    }
                    $html .= '<dd>' . $attributeOptionText . '</dd>';
                }
                $html .= '</dl>';
            }
        }

        return $html;
    }


    public function getSwatchColor($optionId)
    {
        return $this->collectionFactory->create()
                    ->addFieldToFilter('option_id',$optionId)
                    ->getFirstItem();

    }

    /**
     * @param Item   $item
     * @param string $key
     *
     * @return mixed
     */
    protected function getAttributeProduct($item, $key)
    {
        $product = $item->getProduct();
        $value   = $product->getData($key);
        if (empty($value)) {
            $item->setData('product', null);
            $product = $item->getProduct();
        }

        return $product->getData($key);
    }

    /**
     * @param $item
     *
     * @return bool
     */
    public function isRaffle($item)
    {
        $isCheck = (int)$this->getAttributeProduct($item, 'is_check_raffle');
        if ($isCheck == 1) {
            return true;
        }

        return false;
    }

    /**
     * @param Item $item
     *
     * @return float
     */
    public function getSubtotal($item)
    {
        return $item->getQty() * $this->getUnitPrice($item);
    }

    /***
     * @param Item $item
     * @param      $options
     *
     * @return array
     */
    public function getTicketNo($item, $options)
    {
        $tickets = [];
        if (!empty($options)) {
            $isCheck = $this->isRaffle($item);
            foreach ($options as $option) {
                if (
                    !empty($option['option_type']) &&
                    $option['option_type'] == 'checkbox' &&
                    $isCheck
                ) {
                    $tickets[] = $option['value'];
                }
            }
        }

        return $tickets;
    }

    /**
     * @param $quoteItem
     *
     * @return string
     */
    public function renderBrandHtml($quoteItem)
    {
        $html   = '';
        $brands = $this->getAttributeProduct($quoteItem, 'product_brand');
        if (empty($brands)) {
            return $html;
        }

        $brandIds   = explode(',', $brands);
        $connection = $this->getConnection();
        $baseUrl    = $this->_urlBuilder->getBaseUrl();
        $query      = $connection->select()->from(
            $connection->getTableName('magetop_brand'),
            ['brand_id', 'name', 'url_key']
        )->where('brand_id IN (?)', $brandIds);
        $items      = $connection->fetchAll($query);
        if (!empty($items)) {
            $htmlBrands = [];
            foreach ($items as $item) {
                $name         = $item['name'] ?? '';
                $urlKey       = $item['url_key'] ?? '';
                $url          = $baseUrl . sprintf('brand/%s.html', $urlKey);
                $htmlBrands[] = '<a href="' . $url . '" title="' . $name . '">' . $name . '</a>';
            }
            $html = implode(', ', $htmlBrands);
        }

        return $html;
    }

    /**
     * @return AdapterInterface
     */
    public function getConnection()
    {
        if (!$this->connection) {
            $collection       = $this->optionCollection->create();
            $this->connection = $collection->getConnection();
        }

        return $this->connection;
    }

    /**
     * @param $item
     *
     * @return string
     */
    protected function getTicketByQuoteItem($item)
    {
        $customOptions = $this->getCustomOptions($item);
        $tickets       = $this->getTicketNo($item, $customOptions);
        return implode(',', $tickets);
    }

    /**
     * {@inheritdoc}
     * @since 100.1.7
     */
    public function groupProductItems($items)
    {
        $result = $tickets = [];
        /** @var Item $item */
        foreach ($items as $i => $item) {
            $result[$i] = $item;
            $productId  = $item->getProductId();
            $isRaffle   = $this->isRaffle($item);
            if ($isRaffle) {
                unset($result[$i]);
                $key        = 'raffle_' . $productId;
                $lastTicket = $this->getTicketByQuoteItem($item);
                $tickets[]  = $lastTicket;
                $qty        = $lastTicket ? count(explode(',', $lastTicket)) : 1;
                if (isset($result[$key])) {
                    $fistItem  = $result[$key];
                    $ticketNos = implode(', ', $tickets);
                    $fistItem->setData('ticket_no', $ticketNos);
                    $qty = count(explode(', ', $ticketNos));
                    $fistItem->setQty($qty);
                    continue;
                }

                $item->setQty($qty);
                $item->setData('ticket_no', $lastTicket);
                $result[$key] = $item;
            }
        }

        return array_values($result);
    }

    /**
     * @param $item
     *
     * @return float
     */
    public function getUnitPrice($item)
    {
        $option    = $item->getOptionByCode('option_ids');
        $optionIds = (array)$option->getValue();
        if (strpos($option->getValue(), ',') !== false) {
            $optionIds = explode(',', $option->getValue());
        }

        $optionId = reset($optionIds);
        if (!$optionId) {
            return 0;
        }

        /** @var Item\Option $optionValue */
        $optionValue = $item->getOptionByCode('option_' . $optionId);
        if (!$optionValue) {
            return 0;
        }

        $values         = $optionValue->getValue();
        $optionValueIds = (array)$values;
        if (strpos($values, ',') !== false) {
            $optionValueIds = explode(',', $values);
        }

        $collection = $this->valueCollectionFactory->create();
        $collection->addPriceToResult($item->getStoreId());
        $collection->getValuesByOption($optionValueIds);
        $value = $collection->getFirstItem();
        return (float)$value->getPrice();
    }

    /**
     * Retrieves product configuration options
     *
     * @param ItemInterface $item
     *
     * @return array
     */
    public function getCustomOptions(ItemInterface $item)
    {
        $product   = $item->getProduct();
        $options   = [];
        $optionIds = $item->getOptionByCode('option_ids');
        if ($optionIds) {
            $options = [];
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                $option = $product->getOptionById($optionId);
                if ($option) {
                    $itemOption = $item->getOptionByCode('option_' . $option->getId());
                    /** @var $group DefaultType */
                    $group = $option->groupFactory($option->getType())
                                    ->setOption($option)
                                    ->setConfigurationItem($item)
                                    ->setConfigurationItemOption($itemOption);

                    if ('file' == $option->getType()) {
                        $downloadParams = $item->getFileDownloadParams();
                        if ($downloadParams) {
                            $url = $downloadParams->getUrl();
                            if ($url) {
                                $group->setCustomOptionDownloadUrl($url);
                            }
                            $urlParams = $downloadParams->getUrlParams();
                            if ($urlParams) {
                                $group->setCustomOptionUrlParams($urlParams);
                            }
                        }
                    }

                    $options[] = [
                        'label'       => $option->getTitle(),
                        'value'       => $group->getFormattedOptionValue($itemOption->getValue()),
                        'print_value' => $group->getPrintableOptionValue($itemOption->getValue()),
                        'option_id'   => $option->getId(),
                        'option_type' => $option->getType(),
                        'custom_view' => $group->isCustomizedView(),
                    ];
                }
            }
        }

        $addOptions = $item->getOptionByCode('additional_options');
        if ($addOptions) {
            $options = array_merge($options, $this->serializer->unserialize($addOptions->getValue()));
        }

        return $options;
    }

    /**
     * @param $product
     * @param $layout
     *
     * @return string
     */
    public function getPriceHtml($product, $layout)
    {
        $price       = '';
        $priceRender = $layout->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $layout->createBlock(
                Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }
        if ($priceRender) {
            $price = $priceRender->render(
                FinalPrice::PRICE_CODE,
                $product,
                [
                    'display_minimal_price'  => true,
                    'use_link_for_as_low_as' => true,
                    'zone'                   => Render::ZONE_ITEM_LIST
                ]
            );
        }

        return $price;
    }

    /**
     * @return bool
     */
    public function getActionPage()
    {
        if ($this->request->getFullActionName() == 'checkout_cart_index') {
            return true;
        }
        return false;
    }

    /**
     * @param int $price
     *
     * @return string
     */
    public function covertPrice($price = 0)
    {
        return $this->priceFomart->convertAndFormat($price, false, 3, null);
    }

    public function getContinueCartUrl(){
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }
    public function getOptionRaffles($item)
    {
        $quoteItems = $this->checkoutSession->getQuote()->getItems();
        $quoteOptionArray = array();
        foreach($quoteItems as $option)
        {
            if($item->getId() == $option->getId()){
                $quoteOptionArray[] = $option->getTicketNo();
            }
        }

        return implode(',',$quoteOptionArray);
    }

    public function getUrlProduct($item,$url)
    {
        $param = $this->getOptionRaffles($item);
        $param = trim(preg_replace('/\s+/', '',  str_replace(',','%2C',$param)));
        if ($param)
        {

            $url = $url .sprintf('?op=%s&id=%s',$param,$item->getItemId());
        }

        return $url;
    }

    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    public function setProduct($product)
    {
         if (!$this->registry->registry('product'))
        {
            $this->registry->register('product',$product);
        }
        return $this ;
    }
}
