<?php

namespace Ecommage\RaffleTickets\Controller\Action;

use Magento\Checkout\Helper\Cart;

class Add extends \Magento\Checkout\Controller\Cart\Add
{
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var Cart
     */
    protected $helperCart;
    /**
     * @var HelperFreeGift
     */
    protected $helperFreeGift;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param Data $helper
     * @param Cart $helperCart
     * @param HelperFreeGift $helperFreeGift
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Checkout\Model\Session $checkoutSession,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
    \Magento\Checkout\Model\Cart $cart,
    \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
    Cart $helperCart
) {
    parent::__construct(
        $context,
        $scopeConfig,
        $checkoutSession,
        $storeManager,
        $formKeyValidator,
        $cart,
        $productRepository
    );
    $this->helperCart = $helperCart;
}

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD)
     */
    public function execute()
    {
        $product = $this->_initProduct();
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')
            );
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter        = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get(
                        \Magento\Framework\Locale\ResolverInterface::class
                    )->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product->getId()) {
                return $this->goBack();
            }

            $this->cart->addProduct($product, $params);

            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }
            $this->cart->save();

            /**
             * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                $baseUrl = $this->_url->getBaseUrl();
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = __(
                        'You added %1 to your shopping cart.',
                        $product->getName()
                    );
                    $this->messageManager->addSuccessMessage($message);
                }
                return $this->goBack($baseUrl . 'checkout/cart', $product);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNoticeMessage(
                    __($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addErrorMessage(
                        __($message)
                    );
                }
            }
            $url = $this->_checkoutSession->getRedirectUrl(true);
            if (!$url) {
                $cartUrl = $this->helperCart->getCartUrl();
                $url     = $this->_redirect->getRedirectUrl($cartUrl);
            }
            return $this->goBack($url);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            return $this->goBack();
        }
    }
}

