<?php
namespace Custom\CartRule\Observer;
use Magento\Framework\Controller\ResultFactory; 

class OverAllowanceObserver implements \Magento\Framework\Event\ObserverInterface
{
	 public function __construct(     
        \Magento\Checkout\Helper\Cart $cartHelper,
		\Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
		\Magento\Customer\Model\Session $customerSession 
    )
	{
		$this->customerSession  = $customerSession; 
        $this->cartHelper = $cartHelper;
		$this->responseFactory = $responseFactory;
        $this->url = $url;
    }
	
	public function execute(\Magento\Framework\Event\Observer $observer)
	{

		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/over_allowance.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $logger->info('Observer Start --------------------------------');

        $cat_id_liquor = '7';
        $cat_id_tobacco = '258';
        $cat_id_cigarettes = '305';

        $limit_liquor = '3';
        $limit_tobacco = '6';
        $limit_cigarettes = '9';

        $total_qty_liquor = '0';
        $total_qty_tobacco = '0';
        $total_qty_cigarettes = '0';

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$cart = $objectManager->get('\Magento\Checkout\Model\Cart');
		$layout = $objectManager->get('Magento\Framework\View\Layout');

		$allVisibleItems = $cart->getQuote()->getAllVisibleItems();

        foreach ($allVisibleItems as $item) {
            $item_id = $item->getItemId();
            $item_sku = $item->getSku();
            $item_qty = $item->getQty();
            $product_id = $item->getProductId();
        	$logger->info('product_id: '.$product_id);
        	$logger->info('item_sku: '.$item_sku);
        	$logger->info('item_qty: '.$item_qty);

			$product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
			$categories = $product->getCategoryIds();
        	$logger->info('categories: '.json_encode($categories));

			foreach($categories as $category){
			    $cat = $objectManager->create('Magento\Catalog\Model\Category')->load($category);
			    $cat_name = $cat->getName();
			    $cat_id = $cat->getId();

        		if ($cat_id == $cat_id_liquor) {
        			$total_qty_liquor += $item_qty;
        		}
        		if ($cat_id == $cat_id_tobacco) {
        			$total_qty_tobacco += $item_qty;
        		}
        		if ($cat_id == $cat_id_cigarettes) {
        			$total_qty_cigarettes += $item_qty;
        		}
			}
        }
    	$logger->info('total_qty_liquor: '.$total_qty_liquor);
    	$logger->info('total_qty_tobacco: '.$total_qty_tobacco);
    	$logger->info('total_qty_cigarettes: '.$total_qty_cigarettes);

    	$over_allowance_show_popup = '';
    	if ($total_qty_liquor > $limit_liquor || $total_qty_tobacco > $limit_tobacco || $total_qty_cigarettes > $limit_cigarettes) {
    		$over_allowance_show_popup = 'yes';
    	}else{
    		$over_allowance_show_popup = 'no';
		}
    	$logger->info('over_allowance_show_popup: '.$over_allowance_show_popup);

    	// $this->showRedirectPopup();
    	/*$block = $layout->createBlock('Magento\Framework\View\Element\Template')->setTemplate('Magento_Theme::popup/over-allowance-popup.phtml');
		$response = $observer->getEvent()->getData('response');
		$response
		    ->setHeader('Content-Type','text/html')
		    ->setBody($block->toHtml());
		return; */
	    // $block = $layout->createBlock('Magento\Framework\View\Element\Template')->setTemplate('Magento_Theme::popup/over-allowance-popup.phtml')->toHtml();
	    // return $block;
	    $html = $layout->renderNonCachedElement('over_allowance_popup');
	    return $html;

    	/*
    	?>
    	<script type="text/javascript">
		require(["jquery"], function ($) {
		    jQuery(document).ready(function(){
		        $('#over_allowance_popup').addClass("open");
		    });
		});
		</script>
        <?php
        */
    }

}