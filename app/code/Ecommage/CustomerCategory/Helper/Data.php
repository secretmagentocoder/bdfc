<?php
namespace Ecommage\CustomerCategory\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Helper\Data as TaxHelper;

class Data extends AbstractHelper

{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;
    protected $priceCurrency;
    protected $_productRepository;


    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     */
    public function __construct(
        TaxHelper $taxHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        Context $context
    )
    {
        $this->taxHelper = $taxHelper;
        $this->priceCurrency= $priceCurrency;
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->_productRepository = $productRepository;
        parent::__construct($context);
    }


    public function convertToBaseCurrency($price,$covert = "USD",$currentCurrencys = null)
    {
        $currentCurrency = empty($currentCurrencys) ? $this->getBasePrice() : $currentCurrencys;
        $rate = $this->currencyFactory->create()->load($currentCurrency)->getAnyRate($covert);
        if ($rate == false)
        {
            $rate = $this->currencyFactory->create()->load($covert)->getAnyRate($currentCurrency);
        }
        $returnValue = empty((int) $rate) ? $price : $price * $rate;
        return $returnValue;
    }

    public function getProductRepository($id){
        $productRepository = $this->_productRepository->getById($id);
        $price = 0;
        foreach ($productRepository->getOptions() as $option)
        {
            foreach ($option->getValues() as $value){
                $price = number_format($this->taxHelper->getTaxPrice($productRepository, $value->getPrice(), true),3,'.','.');
            }
        }
        return $price;
    }
    
    public function getBasePrice()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    public function getConfigValue()
    {
        return $this->scopeConfig->getValue('currency/options/default',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
