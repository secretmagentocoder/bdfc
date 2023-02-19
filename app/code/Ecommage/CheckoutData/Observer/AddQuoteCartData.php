<?php
namespace Ecommage\CheckoutData\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class AddQuoteCartData implements ObserverInterface
{

    protected $helper;

    protected $request;

    protected $_checkoutSession;

    public function __construct
    (
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig ,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Ecommage\CheckoutData\Helper\Data $helper
)
    {
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        $quoteItems = $this->_checkoutSession->getQuote()->getAllVisibleItems();
        $cartProduct = $observer->getEvent()->getProduct();
        foreach($quoteItems as $quoteItem){
           if($quoteItem->getProductId() == $cartProduct->getId()){
               $quoteItem->setData('is_gift_message',$this->helper->getGiftMessageId($quoteItem));
               $quoteItem->setData('is_gift_wrap',$this->helper->getGwId($quoteItem));
               $quoteItem->setData('is_check_raffle',$cartProduct->getData('is_check_raffle'));
               $quoteItem->setData('product_sku',$cartProduct->getData('sku'));
               $quoteItem->setData('product_brand',$this->helper->getProductBrandHtml($cartProduct));
               $quoteItem->setData('product_size',$cartProduct->getResource()->getAttribute('size')->getFrontend()->getValue($cartProduct));
               $quoteItem->setData('product_price',$this->helper->getPriceHtml($cartProduct));
               $quoteItem->setData('product_price',$this->helper->getPriceHtml($cartProduct));
               if (!empty($quoteItem->getIsVirtual()))
               {
                    $quoteItem->setData('cart_time_raffle',$this->helper->getTimeLocal());
               }
               $quoteItem->save();
           }
        }
    }



}
