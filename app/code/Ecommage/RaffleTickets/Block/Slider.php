<?php
namespace Ecommage\RaffleTickets\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Store\Model\ScopeInterface;


class Slider extends Template {

    protected $_template = "Ecommage_RaffleTickets::slider.phtml";

    const IMAGE = "catalog/upload_image/upload_image_id";

    protected $scopeConfig;
    protected $helper;
    protected $_storeManager;


    public function __construct(
         \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Template\Context $context,
        ScopeConfigInterface  $scopeConfig,
        array $data = [])
    {
         $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

 public function getTopBannerImageUrl($imageName)
    {
        $store = $this->_storeManager->getStore();
        return $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $imageName;
    }
    
    public function getProduct()
    {
        $param = $this->getRequest()->getParam('id');
        return $this->_productRepositoryFactory->create()
                                               ->getById($param);
    }
}
