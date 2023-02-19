<?php
/**
 * 
 * @package Custom_CartRule
 */
declare(strict_types=1);

namespace Custom\CartRule\Plugin\Checkout\CustomerData;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ViewInterface;
use Psr\Log\LoggerInterface;

class CollectTotalCartGuest
{
    protected $checkoutSession;

    public function __construct(
        \Custom\CartRule\Helper\Data $helperApi,
        Http $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        LoggerInterface $logger,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Context $context,
        \Magento\Framework\Json\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->helperApi = $helperApi;
        $this->request =$request;
        $this->logger = $logger;
        $this->_quoteRepository = $quoteRepository;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->_resultJson = $resultJson;
    }

    public function beforeCalculate(\Magento\Checkout\Model\GuestTotalsInformationManagement $subject)
    {
        if (!empty($this->isCheckPage())){
            try {
                $handlingCharges = $this->callApi();
                $handLingTax = (float) $handlingCharges * ($this->helperApi->getCustomDuty()/100);
                $handlingChargesCurrent = $this->helperApi->convertPriceFromBaseCurrencyToCurrentCurrency($handlingCharges);
                $handLingTaxCurrent = $this->helperApi->convertPriceFromBaseCurrencyToCurrentCurrency($handLingTax);

                $this->_checkoutSession->setHandlingCharges($handlingChargesCurrent);
                $this->_checkoutSession->setHandlingChargesTax($handLingTaxCurrent);
                $this->_checkoutSession->setBaseHandlingCharges($handlingCharges);
                $this->_checkoutSession->setBaseHandlingChargesTax($handLingTax);
                $this->_checkoutSession->getQuote()
                ->setHandlingCharges($handlingChargesCurrent)
                ->setHandlingChargesTax($handLingTaxCurrent)
                ->setBaseHandlingCharges($handlingCharges)
                ->setBaseHandlingChargesTax($handLingTax)
                ->save();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

    }

    protected function isCheckPage()
    {
        $page = $this->request->getServer('HTTP_REFERER');
        if (strpos($page,'cart')){
            return true;
        }
        return  false;
    }

    public function callApi()
    {
        return $this->helperApi->getHandling($this->getData());
    }   

    private function getData()
    {
        return [
            "store" => $this->getCodeWebsite(),
            "onhand_qty" => [],
            "cart_id" => $this->_checkoutSession->getQuote()->getId(),
            "items" => $this->setParamItems()
        ];
    }

    private function setParamItems()
    {
        $items = $this->_checkoutSession->getQuote()->getItems();
        $arr = [];
        if (count($items) > 0) {
            foreach ($items as $item) {
                if (!$item->getIsVirtual()) {
                    $arr[] = [
                        'sku' => $item->getSku(),
                        'qty' => number_format((int)$item->getQty())
                    ];
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
