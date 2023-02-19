<?php

namespace Ecommage\RaffleTickets\Plugin\Controller\Cart;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\RedirectInterface;

class Add
{
    public function __construct
    (
        \MageWorx\OptionInventory\Model\StockProvider $stockProvider,
        \Ecommage\CheckoutCart\Helper\Data $helperData,
        \Magento\Catalog\Model\Product $modelProduct,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        RedirectInterface $redirect,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->stockProvider = $stockProvider;
        $this->helperData = $helperData;
        $this->modelProduct = $modelProduct;
        $this->_checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->_registry = $registry;
        $this->resultRedirectFactory = $redirectFactory;
        $this->redirect = $redirect;
        $this->cart = $cart;
        $this->request = $request;
    }

    public function aroundExecute(\Magento\Checkout\Controller\Cart\Add $subject,callable $proceed)
    {
        $id = $this->request->getParam('id',null);
        $op = $this->request->getParam('op',null);
        $productId = $this->request->getParam('product');
        $product = $this->modelProduct->load($productId);
        if ($id && !empty($product->getIsCheckRaffle()) )
        {
            $this->cart->removeItem($id);
            $result = $proceed();
            $option = $this->_checkoutSession->getQuote()->getAllItems();
            $itemQuote = $this->getQuoteItems($option,$product);
            $title = $this->setOption($this->stockProvider->getItemData($itemQuote));
            $param = implode('%2C',$title);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($product->getProductUrl().sprintf('?op=%s&id=%s',$param,$itemQuote->getItemId()));
            return $resultRedirect;
        }else {
            $result = $proceed();
            return $result;

        }

        
    }

    protected function getQuoteItems($items,$product)
    {
        if ($items)
        {
            foreach ($items as $item)
            {
                    if ($item->getProductId() == $product->getEntityId())
                    {
                        return $item;
                    }
            }
        }
        return false;
    }

    public function setOption($options)
    {
        $arr = [];
        foreach ($options as $option)
        {
            $arr[] = $option->getValueTitle();
        }
        return $arr;
    }
}