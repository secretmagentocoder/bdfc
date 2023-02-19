<?php

namespace Ecommage\RaffleTickets\Helper;

use Exception;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Backend\Model\View\Result\Page;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Tax\Model\TaxClass\Source\Product as ProductTaxClassSource;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product\OptionFactory;
use Psr\Log\LoggerInterface;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

/**
 *
 */
class Data extends AbstractHelper
{

    const STATUS = 1;
    const TYPE_PRICE = 1;
    const TYPE_TITLE = 2;
    const TYPE_DELETE = 3;
    const URL_CONFIG = 'ecommage_api/general';
    const PATH = 'ecommage/';
    const QTY = 'qty';
    const DISABLE = 'disable';
    const MANAGE_STOCK = 'manage_stock';

    protected $productSkus = [];

    protected  $log = [];

    protected $options = [];
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Option
     */
    protected $optionProduct;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory
     */
    protected $resourceOption;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    private $navConfigProvider;

    /**
     * @param Option\Value $modelValue
     * @param \MageWorx\OptionInventory\Model\ResourceModel\Product\Option\Value\CollectionFactory $valueCollection
     * @param OptionFactory $productOptionFactory
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param ProductInterfaceFactory $productFactory
     * @param Http $request
     * @param Page $page
     * @param Registry $registry
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepositoryInterface $productRepository
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $resourceOption
     * @param CollectionFactory $productCollectionFactory
     * @param LoggerInterface $logger
     * @param Product $product
     * @param StoreManagerInterface $storeManager
     * @param Option $optionProduct
     * @param Context $context
     */
    public function __construct
    (
       \Ecommage\CustomerCategory\Helper\Data $helperCategory,
        ProductTaxClassSource $productTaxClassSource,
         \Ecommage\CheckoutData\Helper\Data $helper,
        \Magento\Catalog\Model\Product\Option\Value $modelValue,
        \MageWorx\OptionInventory\Model\ResourceModel\Product\Option\Value\CollectionFactory $valueCollection,
        OptionFactory $productOptionFactory,
        WebsiteRepositoryInterface $websiteRepository,
        ProductInterfaceFactory $productFactory,
        Http $request,
        Page $page,
        Registry $registry,
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository,
        ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $resourceOption,
        CollectionFactory $productCollectionFactory,
        LoggerInterface $logger,
        Product $product,
        StoreManagerInterface $storeManager,
        Option $optionProduct,
        Context $context,
        ConfigProvider $navConfigProvider
    ) {
        $this->helperCategory = $helperCategory;
        $this->productTaxClassSource = $productTaxClassSource;
        $this->moduleValue = $modelValue;
         $this->helper = $helper;
        $this->valueCollection = $valueCollection;
        $this->productFactory            = $productFactory;
        $this->productOptionFactory      = $productOptionFactory;
        $this->websiteRepository         = $websiteRepository;
        $this->request                   = $request;
        $this->layout                    = $page;
        $this->_registry                 = $registry;
        $this->scopeConfig               = $scopeConfig;
        $this->productRepository         = $productRepository;
        $this->resource                  = $resourceConnection;
        $this->resourceOption            = $resourceOption;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_logger                   = $logger;
        $this->optionProduct             = $optionProduct;
        $this->product                   = $product;
        $this->_storeManager             = $storeManager;
        $this->navConfigProvider         = $navConfigProvider;
        parent::__construct($context);
    }

    /**
     * @param $optionId
     * @param $sku
     * @return int
     */
    public function getOptionValue($optionId, $sku)
    {
        return $this->valueCollection->create()
                                      ->addFieldToFilter('option_id',$optionId)
                                      ->addFieldToFilter('sku',$sku)->getData();
    }

    /**
     * @param $optionId
     * @param $items
     * @return bool
     * @throws Exception
     */
    public function addValueOption($optionId, $items,$product = null)
    {
        $data = [];

        try {
            if (!$this->getOptionValue($optionId,$items['Ticket_No'])){


                $data = [
                    'option_id' => $optionId,
                    Option::KEY_SORT_ORDER => 1,
                    Option::KEY_TITLE      => $this->setCharacters($items['Ticket_No']),
                    Option::KEY_PRICE_TYPE => "fixed",
                    Option::KEY_PRICE      => 0,
                    Option::KEY_SKU        => strval($items["Ticket_No"]),
                    self::QTY              => !empty($items['Web_Enabled']) ? 1 : 0,
                    self::DISABLE          => !empty($items['Available']) ? 1 : 0,
                    self::MANAGE_STOCK     => 1
                ];
                if (!empty($items['Web_Enabled']) && !empty($items['Available']) ){
                    $data = $this->moduleValue->setData($data);
                    $this->moduleValue->addValue($data)->save();
                    $this->log[$items['PLU_No']][0]['option'] += 1 ;
                }
            }else{
                $connection = $this->resource->getConnection();
                $delete = empty($items['Web_Enabled']) ? 0 : 1;
                $connection->update(
                    'catalog_product_option_type_value',
                    [
                        'delete' => $delete,
                    ],
                    [
                        'option_id' => $optionId,
                        'sku' => $items["Ticket_No"],
                    ]
                );
            }
        }catch (Exception $e)
        {
            $this->_logger->error($e->getMessage());
        }
    }

    public function getOptionValueBySku($optionId,$sku)
    {
        $collection = $this->valueCollection->create()
                                            ->addFieldToFilter('option_id',$optionId)
                                             ->addFieldToFilter('sku',$sku['Ticket_No']);
        if (!empty($collection->getData()))
        {
            foreach ($collection as $item)
            {
                if ($item->getTitle() != $this->setCharacters($item->getSku()))
                {
                    $item->setTitle($this->setCharacters($item->getSku()))->save();
                }
                
                if (!empty($sku['Web_Enabled']))
                {
                    $item->setQty(1)->save();
                }
                if (empty($sku['Web_Enabled']) || empty($sku['Available']))
                {
                    $item->setDisable(0)->save();
                }
                
            }
        }

        return $this;
    }

    /**
     * @param $url
     * @return void
     */
    public function setProduct($url)
    {
        $data = $this->curlCall($url);
        if ($data['value']) {
            try {
                foreach ($data['value'] as $item) {
                    if (!empty($item['Available'])){
                        $this->options[$item['PLU_No']][] = $item['Ticket_No'];
                    }
                    if (!in_array($item['PLU_No'],$this->productSkus)){
                        $this->productSkus[] = $item['PLU_No'];
                    }
                    $product = $this->getCollection($item['PLU_No']);
                    if (empty($product->getItems())) {
                        $product = $this->productFactory->create();
                        $product->setName($item['PLU_Description']);
                        $product->setStatus(1);
                        $product->setPrice($item['Ticket_Price']);
                        $product->setHasOptions(1);
                        $product->setTypeId('virtual');
                        $product->setAttributeSetId(4);
                        $product->setTaxClassId(0);
                        $product->setWebsiteIds($this->getAllWebsite());
                        $product->setStockData([
                                               'use_config_manage_stock' => 1,
                                               'qty' => 10000,
                                               'is_qty_decimal' => 0,
                                               'is_in_stock' => 1,
                                               ]);
                        $product->setSku($item['PLU_No']);
                        $product->setStoreId(!empty($item['Store_No']) ?: 0);
                        $product->setVisibility(Visibility::VISIBILITY_BOTH);
                        $product->save();
                        $this->setAttribute($product->getEntityId(),$item['Series']);
                    } else {
                        $product = $this->productRepository->get($item['PLU_No']);
                        $collection = $this->getProductOptionId($product->getId(),0);
                        if (count($collection))
                        {
                           $this->getOptionValueBySku(current($collection)->getOptionId(),$item);
                            $this->addValueOption(current($collection)->getOptionId(),$item,$product);
                        }
                    }
                }
                $this->saveOption($url);
                if (!empty($data['odata.nextLink'])){
                    $url = $data['odata.nextLink'].'&$format=application/json';
                    $this->setProduct($url);
                }
                $this->setTicketRemaining();
                $this->deleteProduct();
            } catch (Exception $e) {
                $this->_logger->error($e->getMessage());
            }
        }
    }

    /**
     * @param $id
     * @param $storeId
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface[]
     */
    public function getProductOptionId($id, $storeId)
    {
        return $this->resourceOption->create()
               ->getProductOptions($id,$storeId);
    }

    /**
     * @param $option
     * @param $value
     * @param $product
     * @return $this
     */
    private function isCheckUpdate($option, $value, $product = null)
    {
        if ($option)
        {
            $array = [];
            foreach ($option as $item){
                foreach ($item->getValues() as $data) {
                    try {
                        if (number_format($data->getPrice()) != $item['Ticket_Price']) {
                            $connection = $this->resource->getConnection();

                            $connection->update(
                                'catalog_product_option_type_price',
                                [
                                    'price' => $value['Ticket_Price'],
                                ],
                                [
                                    'option_type_id' => $data->getOptionTypeId(),
                                ]
                            );
                        }
                    } catch (Exception $e) {
                        $this->_logger->error($e->getMessage());
                    }
                }
            }
        }
        return $this;
    }

    protected function setTicketRemaining()
    {
        foreach ($this->options as $key => $item)
        {
            $product = $this->product->load($this->product->getIdBySku($key));
            $product->setCustomAttribute('ticket_remaining',strval(count($item)))->save();
        }
    }

    /**
     * @return array
     */
    public function getAllWebsite()
    {
        $websiteId = [];
        foreach (  $this->websiteRepository->getList() as $item)
        {
            $websiteId[] = $item->getWebsiteId();
        }

        return $websiteId;
    }


    /**
     * @param $id
     * @param $series
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function setAttribute($id, $series)
    {
        $product  = $this->productRepository->getById($id);
        if ($product)
        {
            try {
                $product->setCustomAttribute('series', $series);
                $product->setCustomAttribute('is_check_raffle',1);
                $product->save();
            }catch (Exception $e)
            {
                $this->_logger->error($e->getMessage());
            }
        }
        return $this;
    }


    /**
     * @param $url
     * @return $this|array
     */
    public function curlCall($url)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
                CURLOPT_USERPWD => sprintf("%s:%s", $this->getConfigUserName(), $this->getConfigPassword()),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ]);
            $response_data = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response_data, true);
            if (is_array($response) && count($response) > 0) {
                return $response;
            } else {
                return [];
            }
        } catch (Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return  $this;
    }

    /**
     * @param $sku
     * @return Collection|AbstractDb
     */
    public function getCollection($sku)
    {
        return $this->_productCollectionFactory->create()->addFieldToFilter('sku', $sku);
    }

    /**
     * @param $product
     * @return $this
     */
    public function saveOption($url)
    {
        $api = $this->curlCall($url);
         $arrSku = array_column($api['value'],'PLU_No');
        if (is_array($arrSku) && count($arrSku) > 0) {
            try {
                foreach (array_unique($arrSku) as $sku){
                    $product = $this->productRepository->get($sku);
                    if (count($product->getOptions()) == 0){
                        $values = $this->setOption($product,$url);
                        $optionArray = [
                            'title' => 'Raffle Ticket',
                            'type' => Option::OPTION_TYPE_CHECKBOX,
                            'is_require' => self::STATUS,
                            'sort_order' => 1,
                            'price' => 0,
                            'price_type' => 'fixed',
                            'sku' => $product->getSku(),
                            'max_characters' => 0,
                            'product_id' => $product->getId(),
                            'values' => $values
                        ];

                            $option = $this->productOptionFactory->create();
                            $option->setProductId($product->getId())
                                   ->setStoreId($product->getStoreId())
                                   ->addData($optionArray);
                            $product->addOption($option);
                            $product->save();
                            $this->log[$product->getSku()][] = ['sku' => $product->getSku(),'option'=> count($values)];
                    }
                }
            } catch (Exception $e) {
                $this->_logger->error($e->getMessage());
            }
        };
        return $this;
    }

    /**
     * @param $product
     * @return array
     */
    public function setOption($product,$url)
    {
          $options = [];
          $data = $this->curlCall($url);
        if (!empty($data['value'])) {
            foreach ($data['value'] as $item) {
                if ($item['PLU_No'] == $product->getSku() && !empty($item['Web_Enabled'] && !empty($item['Available']))) {
                    $options[$item['Ticket_No']] = [
                        Option::KEY_SORT_ORDER => 1,
                        Option::KEY_TITLE => $this->setCharacters($item['Ticket_No']),
                        Option::KEY_PRICE_TYPE => "fixed",
                        Option::KEY_PRICE => 0,
                        Option::KEY_TYPE => Option::OPTION_TYPE_FIELD,
                        Option::KEY_SKU => $item['Ticket_No'],
                        self::QTY => !empty($item['Web_Enabled']) ? 1 : 0,
                        self::DISABLE => !empty($item['Available']) ? 1 : 0,
                        self::MANAGE_STOCK => 1
                    ];

                }
            }
        }

        return $options;
    }


    /**
     * @param $characters
     * @return string
     */
    public function setCharacters($characters)
    {
        if (strlen(trim($characters)) == 1) {
            return '00' . $characters;
        }
        if (strlen(trim($characters)) == 2) {
            return '0' . $characters;
        }
        return $characters;
    }

    public function deleteProduct()
    {
        $collection = $this->getProductCollections();
        foreach ($collection as $product)
        {
            try {
                if (!in_array($product->getSku(),$this->productSkus))
                {
                    $this->productRepository->delete($product);
                }
            }catch (Exception $e)
            {
                $this->_logger->error($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * @param $type
     * @param $data
     * @param $options
     *
     * @return $this
     */
    public function updateOption($type, $data = null, $options = null, $product = null)
    {
        if ($type) {
            $connection = $this->resource->getConnection();
            switch ($type) {
                case 1:
                    try {
                        $connection->update(
                            'catalog_product_option_type_price',
                            [
                                'price' => $data['price']
                            ],
                            [
                                'option_type_id' => $options->getOptionTypeId(),
                            ]
                        );
                    } catch (Exception $e) {
                        $this->_logger->error($e->getMessage());
                    }
                case 2:
                    try {
                        $connection->update(
                            'catalog_product_option_type_title',
                            [
                                'title' => $data['title']
                            ],
                            [
                                'option_type_id' => $options->getOptionTypeId(),
                            ]
                        );
                    } catch (Exception $e) {
                        $this->_logger->error($e->getMessage());
                    }
                case 3:
                    try {
                        $connection->delete('catalog_product_option',
                                            [
                                                'product_id' => $product->getEntityId(),
                                                'sku'        => $product->getSku(),
                                                'type'       => Option::OPTION_TYPE_DROP_DOWN
                                            ]
                        );
                    } catch (Exception $e) {
                        $this->_logger->error($e->getMessage());
                    }
            }
        }
        return $this;
    }

    /**
     * @param $product
     *
     * @return void
     */
    public function getOptionProduct($product)
    {
        $data = $this->getOption($product, $this->getOptionId($product));
        if ($data) {
            foreach ($data as $item) {
                $optionApi = $this->setOption($product);
                if (array_key_exists($item->getTitle(), $optionApi)) {
                    if ($optionApi[$item->getTitle()]['title'] != $item->getTitle()) {
                        $this->updateOption(self::TYPE_TITLE, $optionApi[$item->getTitle()], $item);
                    }
                } else {
                    $this->deleteOption($item);
                }
            }
        }
    }

    /**
     * @param $product
     * @param $option
     *
     * @return array
     */
    public function getOption($product, $option)
    {
        $arr = [];
        if ($product && $option) {
            $customOptions = $this->optionProduct->getProductOptions($product);
            $arr           = $customOptions[$option->getOptionId()]->getValues();
        }
        return $arr;
    }

    /**
     * @param $product
     *
     * @return DataObject
     */
    public function getOptionId($product)
    {
        return $this->resourceOption->create()
                                    ->addProductToFilter($product)
                                    ->getFirstItem();
    }

    /**
     * @param $options
     *
     * @return $this
     */
    public function deleteOption($options)
    {
        if ($options) {
            $connection = $this->resource->getConnection();
            try {
                $connection->delete(
                    'catalog_product_option_type_price',
                    [
                        'option_type_id' => $options->getOptionTypeId(),
                    ]
                );
            } catch (Exception $e) {
                $this->_logger->error($e->getMessage());
            }
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfigLinkApi()
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $linkApi = $host.'/Company(%27'.$company.'%27)/TicketList?$format=application/json';
        return $linkApi;
    }

    /**
     * @return mixed
     */
    public function getConfigUserName()
    {
        return $this->navConfigProvider->getUser();
    }

    /**
     * @return mixed
     */
    public function getConfigPassword()
    {
        return $this->navConfigProvider->getPassword();
    }

    /**
     * @return mixed
     */
    public function getConfigIsCheck($param = null)
    {
        return true;
    }

    /**
     * @return mixed|null
     */
    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    /**
     * @return string
     */
    public function getBLockHtml()
    {
        return $this->layout->getLayout()->createBlock('Ecommage\RaffleTickets\Block\Raffle\CategoryTicket')->toHtml();
    }

    /**
     * @param $product
     * @return array
     */
    public function getAttributeValue($product)
    {
        $value = [];
        if (!empty($product->getCustomAttribute('is_check_raffle')->getValue() && $product->getTypeId() == 'virtual')) {
            $value = $product->getCustomAttribute('is_check_raffle')->getValue();
        }
        return $value;
    }

    /**
     * @return mixed
     */
    public function getAllOption()
    {
        return $this->getCurrentProduct()->getOptions();
    }

    /**
     * @param $options
     * @return array
     */
    public function getOptionProducts($options)
    {
        $arr = [];
        foreach ($options as $item) {
            $arr[$item->getSku()] = $item->getOptionTypeId();
        }
        ksort($arr);
        return $arr;
    }

    /**
     * @param $options
     * @return array
     */
    public function setOptionNumber($options)
    {
        $arr     = [];
        $product = $this->getCurrentProduct();
        foreach ($options as $item) {
            if ($item->getSku() == $product->getSku()
                && $item->getType() == Option::OPTION_TYPE_CHECKBOX) {
                foreach ($item->getValues() as $option) {
                    if (empty($this->getQtyOption($option))) {
                        $arr[$option->getSku()] = $option->getOptionTypeId();
                    }
                }
            }
        }
        ksort($arr);
        return $arr;
    }


    /**
     * @param $options
     * @return array
     */
    public function getTicketOptionsRemaining($options)
    {
        $arr = [];
        foreach ($options as $item) {
            if ( $item->getType() == Option::OPTION_TYPE_CHECKBOX) {
                foreach ($item->getValues() as $option) {
                    if (empty($this->getQtyOption($option))) {
                        $arr[$option->getSku()] = $option->getOptionTypeId();
                    }
                }
            }
        }
        ksort($arr);
        return $arr;
    }

    /**
     * @param $number
     * @return array
     */
    public function getEvenNumber($number)
    {

        $arrNumber = [];
        if (is_array($number) && count($number) > 0) {
            foreach ($number as $key => $value) {
                if ($key % 2 == 0) {
                    $arrNumber['even'][] = $value;
                }
                if ($key % 2 != 0) {
                    $arrNumber['odd'][] = $value;
                }
            }
        }
        return $arrNumber;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getBlockOption()
    {
        return $this->layout->getLayout()->createBlock('Magento\Catalog\Block\Product\View\Options');
    }

    /**
     * @return bool
     */
    public function getActionName()
    {
        $product = $this->getCurrentProduct();
        if ($this->request->getFullActionName() == 'catalog_product_view' && !empty($product->getIsCheckRaffle())) {
            return true;
        }
        return false;
    }

    public function getFullActionName()
    {
        if ($this->request->getFullActionName() == 'catalog_product_view')
        {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getImageBanner()
    {
        return $this->scopeConfig->getValue(self::URL_CONFIG . '/upload_image_id', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getAlsoBuy()
    {
        $param      = $this->request->getParam('id', null);
        $collection = [];
        if ($param) {
            $collection = $this->getProductCollections();
            $collection->addAttributeToFilter('entity_id', ['neq' => $param]);
        }

        return $collection;
    }

    protected function getProductCollections()
    {
        return $this->_productCollectionFactory->create()
                             ->addAttributeToSelect('*')
                             ->addAttributeToFilter('is_check_raffle', 1)
                             ->addAttributeToFilter('type_id', 'virtual');
    }

    /**
     * @param $imageName
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTopBannerImageUrl($imageName)
    {
        $store = $this->_storeManager->getStore();
        return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $imageName;
    }

    /**
     * @param $id
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct($id)
    {
        return $this->productRepository->getById($id);
    }

    /**
     * @param $data
     * @param $type
     * @param $id
     * @return string
     */
    public function setDisplay($data, $type, $id)
    {
        $display = 'show_option';
        if (!empty($data) && !empty($type) && $type != 'all') {
            $key = $type == 'odd' ? 'even' : 'odd';
            if ($key == 'odd' && $id % 2 != 0) {
                $display = 'hide_option';
            }
            if ($key == 'even' && $id % 2 == 0) {
                $display = 'hide_option';
            }
        }
        return $display;
    }

    /**
     * @param $data
     * @param $type
     * @param $id
     * @return string
     */
    public function setClass($data, $type, $id)
    {
        $display = '';

        if (!empty($data) && !empty($type) && $type != 'all') {
            if ($id % 2 != 0) {
                $display = 'odd';
            }
            if ($id % 2 == 0) {
                $display = 'even';
            }
        } else {
            $display = 'all';
        }
        return $display;
    }

    /**
     * @param $option
     *
     * @return string
     */
    public function getQtyOption($option)
    {
        $disable = '';
        if (empty($option->getDisable()) || empty(number_format(($option->getQty())))) {
            $disable = 'disabled';
        }

        return $disable;
    }

    /**
     * @param $number
     *
     * @return array
     */
    public function getCountOption($number)
    {
        $arrNumber = [];
        if (is_array($number) && count($number) > 0) {
            foreach ($number as $key => $value) {
                if ($key % 2 == 0) {
                    $arrNumber['even'][] = $value;
                }
                if ($key % 2 != 0) {
                    $arrNumber['odd'][] = $value;
                }
            }
        }

        return $arrNumber;
    }

        /**
     * @return array
     */
    public function getTaxClassProduct()
    {
        $storeId = [];
        $storeLists = ['1E', '2E', '8'];
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $url = $host.'/Company('.'\''.$company.'\''.')/WebStore?$format=application/json';
        $api = $this->helper->curlCall($url);
        $storeVatIds = [];
        if ($api)
        {
            foreach ($api as $key => $value) {
                $storeId = $value['No'];
                $StoreVATBusPostGr = $value['Store_VAT_Bus_Post_Gr'];
                if(in_array($storeId, $storeLists))
                {
                    $storeVatIds[$storeId] =  $StoreVATBusPostGr;
                }
            }
        }

        return $storeVatIds;
    }

    /**
     * @param $sub
     * @param $param
     *
     * @return string
     */
    protected function setTitleTax($sub, $param)
    {
        $text  = $this->getTaxClassProduct();

        $title = '';
        $subs = '__'. ucfirst($sub);
        if (strpos($subs,ucfirst('departures'))  && key_exists('1E',$text))
        {
            $title = $text['1E']. ' - ' . $param;
        }
        if (strpos($subs,ucfirst('arrivals'))  && key_exists('2E',$text))
        {
            $title = $text['2E']. ' - ' . $param;
        }
        if (strpos($subs,ucfirst('delivery'))  && key_exists('8',$text))
        {
            $title = $text['8']. ' - ' . $param;
        }

        return $title;
    }

     /**
     * @return mixed
     */
    public function getConfigBreadcrumbsUrl()
    {
        return $this->scopeConfig->getValue('ecommage_display_video/general/cmspage', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array|array[]
     */
    protected function getTaxClass()
    {
        return $this->productTaxClassSource->getAllOptions();
    }

    /**
     * @param $param
     *
     * @return mixed|string
     */
    protected function setTaxProductByStore($param)
    {
        if ($this->getTaxClass() && $param){
            foreach ($this->getTaxClass() as $taxClass)
            {

                if (ucfirst($param) == ucfirst($taxClass['label']))
                {
                    return $taxClass['value'];
                }
            }
        }
        return '';
    }

    /**
     * @param $sku
     * @param $storeName
     *
     * @return mixed|string
     */
    public function getTaxByApi($sku, $storeName)
    {
        $data = '';
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $url = $host.'/Company('.'\''.$company.'\''.')/WebItemList?$format=application/json&$filter=No%20eq%20%27'.$sku.'%27';
        $repo = $this->helper->curlCall($url);
        if (!empty($repo)){
            $title = $this->setTitleTax($storeName,$repo[0]['VAT_Prod_Posting_Group']);
            $data = $this->setTaxProductByStore($title);
        }

        return $data;
    }


    public function setTaxProducts()
    {
        try {
            $collection = $this->getCollectionProduct();
            foreach ($collection as $product)
            {
                foreach ($this->_storeManager->getStores() as $store)
                {
                     $this->productRepository->get($product->getSku(),false,$store->getId())
                                                   ->setTaxClassId($this->getTaxByApi($product->getSku(),$store->getName() ?? 0))
                                                   ->save();
                }
            }
        }catch (\Exception $e)
        {
            $this->_logger->error($e->getMessage());
        }
    }

    public function getCollectionProduct()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToFilter('is_check_raffle',1)
                   ->addFieldToFilter('type_id','virtual');
        return $collection;
    }

    public function getPriceTicket($id)
    {
        $currentCurrency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        return sprintf('%s %s',$currentCurrency,$this->helperCategory->getProductRepository($id));
    }

}
