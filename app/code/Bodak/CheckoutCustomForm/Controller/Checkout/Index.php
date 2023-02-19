<?php

namespace Bodak\CheckoutCustomForm\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Setup\Exception;
use Psr\Log\LoggerInterface;
use Bodak\CheckoutCustomForm\Api\Data\CustomFieldsInterface;

class Index extends Action
{
    public function __construct
    (
        LoggerInterface $logger,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Json\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Bodak\CheckoutCustomForm\Helper\Data $helperApi
    )
    {
        $this->helperApi = $helperApi;
        $this->_urlInterface = $urlInterface;
        $this->logger = $logger;
        $this->_quoteRepository = $quoteRepository;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->_resultJson = $resultJson;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->setQtyOnHandQuote(json_encode($this->setDataQty()));
            try {
                $handlingCharges = $this->callApi() ?? 0 ;
                $handLingTax = (float) $handlingCharges * ($this->helperApi->getCustomDuty()/100);
                $handlingChargesCurrent = $this->helperApi->convertPriceFromBaseCurrencyToCurrentCurrency($handlingCharges);
                $handLingTaxCurrent = $this->helperApi->convertPriceFromBaseCurrencyToCurrentCurrency($handLingTax);

                $this->_checkoutSession->setIsLoad(1);
                $this->_checkoutSession->setHandlingCharges($handlingChargesCurrent);
                $this->_checkoutSession->setHandlingChargesTax($handLingTaxCurrent);
                $this->_checkoutSession->setBaseHandlingCharges($handlingCharges);
                $this->_checkoutSession->setBaseHandlingChargesTax($handLingTax);
                $this->_checkoutSession->getQuote()
                ->setHandlingCharges($handlingChargesCurrent)
                ->setHandlingChargesTax($handLingTaxCurrent)
                ->setBaseHandlingCharges($handlingCharges)
                ->setBaseHandlingChargesTax($handLingTax)->save();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
    }

    protected function setQtyOnHandQuote($qtyData){
        try{
            $this->_checkoutSession->setQtyOnHand($qtyData);
        }catch(Exception $e){
            $this->logger->error($e->getMessage());
        }
    }

    public function callApi()
    {
        return $this->helperApi->getHading($this->getData());
    }

    private function getData()
    {
        return [
            "store" => $this->getCodeWebsite(),
            "onhand_qty" => $this->setDataQty(),
            "items" => $this->setParamItems()
        ];
    }

    private function setParamItems()
    {
        $items = $this->_checkoutSession->getQuote()->getItems();
        $arr = [];
        if (count($items) > 0)
        {
            foreach ($items as $item)
            {
                if(!$item->getIsVirtual()){
                    $arr[] = [
                        'sku' => $item->getSku(),
                        'qty' => number_format((int)$item->getQty())
                        ] ;
                }
            }
        }

        return $arr;
    }

    private function setDataQty()
    {
        $data = $this->getRequest()->getParam('data');
        $arr = [];
        if ($data){
            foreach ($data as $items)
            {
                if (str_contains($items['name'],'_api')){
                    $arr[strtoupper(rtrim($items['name'],'_api'))] = $items['value'];
                }
            }
        }

        return $arr;
    }

    public function getCodeWebsite()
    {
        return $this->_storeManager->getStore()->getCode();
    }
}
