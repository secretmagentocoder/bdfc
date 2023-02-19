<?php

namespace Ecommage\CheckoutData\Helper;

use Exception;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class Data extends AbstractHelper
{
    const STATUS = 1;
    const PATH = 'ecommage/create_airline_list';

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var ConfigProvider
     */
    private $navConfigProvider;

    /**
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\Framework\App\Request\Http $request
     * @param Page $page
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param Context $context
     * @param ConfigProvider $navConfigProvider
     */
    public function __construct
    (
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Framework\App\Request\Http           $request,
        Page                                          $page,
        \Magento\Framework\Registry                   $registry,
        ScopeConfigInterface                          $scopeConfig,
        ResourceConnection                            $resourceConnection,
        LoggerInterface                               $logger,
        StoreManagerInterface                         $storeManager,
        \Magento\Framework\Pricing\Helper\Data        $priceHelper,
        Context                                       $context,
        ConfigProvider                                $navConfigProvider
    )
    {
        $this->priceHelper = $priceHelper;
        $this->websiteRepository = $websiteRepository;
        $this->request = $request;
        $this->layout = $page;
        $this->_registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->resource = $resourceConnection;
        $this->_logger = $logger;
        $this->_storeManager = $storeManager;
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function curlCall($apiLink)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $apiLink,
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
                return $response['value'];
            }
            return [];
        } catch (Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getAllWebsite()
    {
        $websiteId = [];
        foreach ($this->websiteRepository->getList() as $item) {
            $websiteId[] = $item->getWebsiteId();
        }
        return $websiteId;
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

    public function getPriceHtml($product)
    {
        $productPriceHtml = '';
        if ($product->hasSpecialPrice()) {
            $productPriceHtml .= '<span class="price-discount">';

            $_price = $product->getPrice();
            $_finalPrice = $product->getFinalPrice();
            if ($_finalPrice < $_price) {
                $_savingPercent = 100 - round(($_finalPrice / $_price) * 100);
                $_savingPrice = $_price - $_finalPrice;
                $savingPrice = number_format((float)$_savingPrice, 2, '.', '');
                $productPriceHtml .= '<span class="price-container">';
                $productPriceHtml .= '<span class="price">';
                $productPriceHtml .= 'Save ' . $_savingPercent . '%';
                $productPriceHtml .= '</span>';
                $productPriceHtml .= '</span>';
            }
            $productPriceHtml .= '</span>';

            $productPriceHtml .= '<del class="old-price">';
            $productPrice = $this->priceHelper->currency($product->getPrice(), true, false);
            $productPriceHtml .= '<span class="price-wrapper"><span class="price">' . $productPrice . '</span></span>';
            $productPriceHtml .= '</del>';

            $productPriceHtml .= '<span class="special-price">';
            $productPrice = $this->priceHelper->currency($product->getFinalPrice(), true, false);
            $productPriceHtml .= '<span class="price-wrapper"><span class="price">' . $productPrice . '</span></span>';
            $productPriceHtml .= '</span>';
        } else {
            $productPriceHtml .= '<span class="regular-price">';
            $productPrice = $this->priceHelper->currency($product->getFinalPrice(), true, false);
            $productPriceHtml .= '<span class="price-wrapper"><span class="price">' . $productPrice . '</span></span>';
            $productPriceHtml .= '</span>';
        }

        return $productPriceHtml;
    }

    public function getGiftMessageId($quote)
    {
        $isGiftMessage = 'yes';
        if ($quote->getGiftMessageId() == null) {
            $isGiftMessage = "no";
        }

        return $isGiftMessage;
    }

    public function getGwId($quote)
    {
        $isGiftWrap = 'yes';
        if ($quote->getGwId() == null) {
            $isGiftWrap = "no";
        }
        return $isGiftWrap;
    }

    public function getProductBrandHtml($product)
    {
        $connection = $this->resource->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $home_url = $this->_storeManager->getStore()->getBaseUrl();
        $brand_id = $product->getData('product_brand');
        $product_brand_html = '';

        if ($brand_id) {
            $query = $connection->select()->from('magetop_brand', ['*'])->where('brand_id = ?', $brand_id);
            $query_result = $connection->fetchRow($query);
            $brand_name = $query_result['name'];
            $brand_url_key = $query_result['url_key'];
            $brand_url = $home_url . 'brand/' . $brand_url_key . '.html';
            $product_brand_html = '<a href="' . $brand_url . '" title="' . $brand_name . '">' . $brand_name . '</a>';
        }

        return $product_brand_html;
    }

    public function quote_item_options($item_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        // $itemsVisible = $cart->getQuote()->getAllVisibleItems();
        // $items = $cart->getQuote()->getAllItems();
        $session = $objectManager->get('\Magento\Checkout\Model\Session');
        $quote_repository = $objectManager->get('\Magento\Quote\Api\CartRepositoryInterface');
        $qid = $session->getQuoteId();
        if (empty($qid)) {
            return '';
        }
        $quote = $quote_repository->get($qid);
        $items = $quote->getAllItems();

        $product_options = '';
        foreach ($items as $item) {
            if ($item->getId() == $item_id) {
                $attribute = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Eav\Attribute');
                $product = $objectManager->create('\Magento\Catalog\Model\Product')->load($item->getProductId());
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                if (!empty($options)) {
                    if (isset($options['info_buyRequest']['super_attribute'])) {
                        $super_attribute = $options['info_buyRequest']['super_attribute'];
                        $product_options .= '<dl class="item-options">';
                        foreach ($super_attribute as $key => $value) {
                            $attribute_id = $key;
                            $attribute_option_id = $value;

                            $attribute_model = $attribute->load($attribute_id);
                            $attribute_code = $attribute_model->getAttributeCode();
                            $attribute_label = $attribute_model->getFrontendLabel();

                            $_attributeId = $product->getResource()->getAttribute($attribute_code);
                            if ($_attributeId->usesSource()) {
                                $attribute_option_text = $_attributeId->getSource()->getOptionText($attribute_option_id);
                            }
                            $product_options .= '<dt>' . $attribute_label . '</dt>';
                            $product_options .= '<dd>' . $attribute_option_text . '</dd>';

                        }
                        $product_options .= '</dl>';
                    }
                }
            }
        }

        return $product_options;
    }
    public function getTimeLocal()
    {
        $timeZone = new \DateTimeZone($this->getTimezone());
        $timeLocal = new \DateTime('now', $timeZone);
        return $timeLocal->format('H:i:s');
    }

    public function getTimezone()
    {
        return $this->scopeConfig->getValue(
            'general/locale/timezone', ScopeInterface::SCOPE_STORE
        );
    }

}
