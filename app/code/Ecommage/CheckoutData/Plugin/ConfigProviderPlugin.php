<?php

namespace Ecommage\CheckoutData\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Eav\Model\Config;
use Ecommage\CheckoutForm\Helper\Data;
use Magento\Framework\View\LayoutInterface;

class ConfigProviderPlugin
{
    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $quoteItem;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    protected $helperPersonal;

    /**
     * @param CustomerRepository $customerRepository
     * @param CustomerSession $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        Data $helper,
        Config                                             $eavConfig,
        LayoutInterface $layout,
        \Ecommage\CustomerPersonalDetail\Helper\Data $helperPersonal
    )
    {
        $this->helperPersonal = $helperPersonal;
        $this->layout = $layout;
        $this->serializer = $serializer;
        $this->product = $product;
        $this->quoteItem = $quoteItem;
        $this->_storeManager = $storeManager;
        $this->helper = $helper;
        $this->_eavConfig = $eavConfig;
    }

    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {
        $result['cms_block_collection_point'] = $this->getBlockDeparture();
        $result['cms_block_arrival_collection_point'] = $this->getBlockArrival();
        $result['select_your_lounge'] = $this->getLoungeOptions();
        $result['nationalities'] = $this->helperPersonal->getNationalities();
        return $result;
    }

    protected function getBlockDeparture(){
         $block = $this->layout
            ->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('Ecommage_CheckoutData::widget/departure_collection_point.phtml');
         return $block->toHtml();
    }

    protected function getBlockArrival(){
        $block = $this->layout
            ->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('Ecommage_CheckoutData::widget/arrival_collection_point.phtml');
        return $block->toHtml();
    }

    public function getLoungeOptions(){
        $loungeAttr = $this->_eavConfig->getAttribute('customer', 'select_your_lounge');
        $nationalOptions = [
            'label'=>$loungeAttr->getStoreLabel(),
            'options'=>$loungeAttr->getSource()->getAllOptions()
        ];
        return $nationalOptions;
    }
}
