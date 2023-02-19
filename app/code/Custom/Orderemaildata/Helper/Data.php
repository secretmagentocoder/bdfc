<?php
namespace Custom\Orderemaildata\Helper;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
 
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $currentData;
    protected $storeManagerInterface;
    protected $ImageFactory;
    protected $imageHelper;
    protected $checkoutSession;

    public function __construct(
        \Magento\Sales\Model\Order $orderData,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Helper\ImageFactory $ImageFactory
        ) {
            $this->currentData = $orderData;
            $this->storeManagerInterface = $storeManagerInterface;
            $this->ImageFactory = $ImageFactory;
            $this->imageHelper = $imageHelper;
            $this->checkoutSession = $checkoutSession;
        }

    public function getLastOrderDetails($order)
        {

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/helperData.log');
            // $logger = new \Zend_Log();
            // $logger->addWriter($writer);
            // $logger->info('helper');

            $html = '<tr>
                        <th colspan="2"></th>
                        <th style="text-align: center;">UNIT PRICE</th>
                        <th style="text-align: center;">QTY</th>
                        <th style="text-align: right;">TOTAL</th>
                    </tr>';

            foreach($order->getAllItems() as $item){
                // $logger->info(print_r($item->getData(), true));
                $product       = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getProductId());
                $store         = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
                $imageUrl      = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
                // $product_brand = $product->getResource()->getAttribute('product_brand')->getFrontend()->getValue($product);

                $html .= '<tr>
                            <td style="padding: 8px 10px;  width:10%; text-align: center;">
                                <img src="' . ($imageUrl) . '" alt="chivas" width="100%">
                            </td>
                            <td style="padding: 8px 20px;  width: 50%; text-align: left;">
                                <h5 style="color: rgb(129, 129, 129); padding: 0px; margin: 0px;"></h5>
                                <h4 style="padding: 10px 0px;margin: 0;">' . ($item->getName()) . '</h4>
                                <p style="padding: 0px 0px;margin: 0; font-size:15px;"> Item No: ' . ($item->getSku()) . '</p>
                                <p style="padding: 0;margin: 0 ; font-size:15px;">  </p>
                            </td>
                            <td style="padding: 8px 0px;  width: 20%; text-align: center;">';
                            if($item->getOriginalPrice() != $item->getPrice()){
                                $html .= '<h5 style="color: #ee4c50; padding: 0;margin: 0; ">Save BHD ' . ($item->getOriginalPrice() - $item->getPrice()) . ' </h5>
                                <p style="padding: 0;margin: 0;  font-size:15px;">
                                    <del> BHD' . ($item->getOriginalPrice()) . '</del>
                                </p>';

                            }
                            $html .= '<h4 style="padding: 0;margin: 0;  font-size:15px;"> BHD ' . ($item->getPrice()) . '</h4>
                            </td>
                            <td style="padding: 8px 0px;  width: 5%; text-align: center;"> ' . ($item->getQtyOrdered()) . '</td>
                            <td style="padding: 8px 0px;  width: 15%; text-align: center;"> BHD ' . ($item->getRowTotal()) . '</td>
                        </tr>';
            }

            // $logger->info('helper');
            // $logger->info($product_brand);
            // $logger->info('after helper');
            return $html;
        }
}