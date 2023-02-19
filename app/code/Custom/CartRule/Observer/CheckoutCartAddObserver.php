<?php

namespace Custom\CartRule\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * CheckoutCartAddObserver
 */
class CheckoutCartAddObserver implements ObserverInterface
{
    protected $_layout;
    protected $_storeManager;
    protected $_request;
    
    /**
     * __construct
     *
     * @param \Magento\Store\Model\StoreManagerInterface storeManager
     * @param \Magento\Framework\View\LayoutInterface layout
     * @param \Magento\Framework\App\RequestInterface request
     * @param \Magento\Framework\Serialize\SerializerInterface serializer
     *
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->serializer = $serializer;
    }
    
    /**
     * execute
     *
     * @param EventObserver observer
     *
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $postValue = $this->_request->getParams();
        $postValue['color'] = 'test';
        // product_color_shade is input field in product view page
        if (isset($postValue['color']) && $postValue['color']) {
            $item = $observer->getQuoteItem();

            $colorShade = [];
            $colorShade[] = [
                'label' => 'Color',
                'value' => $postValue['color'],
            ];

            if (count($colorShade) > 0) {
                $item->addOption([
                    'product_id' => $item->getProductId(),
                    'code' => 'additional_options',
                    'value' => $this->serializer->serialize($colorShade),
                ]);
            }
        }
    }
}