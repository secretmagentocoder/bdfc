<?php

namespace Custom\CartRule\Controller\OverAllowance;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $resultJsonFactory;

	protected $resourceConnection;

    protected $eavSetupFactory;


	public function __construct(
        Context $context, 
        JsonFactory $resultJsonFactory, 
        ResourceConnection $resourceConnection, 
        // EavSetupFactory $eavSetupFactory,
        array $data = array()
    ){
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        // $this->eavSetupFactory = $eavSetupFactory;
        parent::__construct($context);
    }

    public function execute()
    {

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

        $totalItems = $cart->getQuote()->getItemsCount();
        $allVisibleItems = $cart->getQuote()->getAllVisibleItems();
        
        foreach ($allVisibleItems as $item) {
            $item_id = $item->getItemId();
            $item_sku = $item->getSku();
            $item_qty = $item->getQty();
            $product_id = $item->getProductId();
           
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
            $categories = $product->getCategoryIds();

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
      
        $over_allowance_show_popup = '';
        if ($total_qty_liquor > $limit_liquor || $total_qty_tobacco > $limit_tobacco || $total_qty_cigarettes > $limit_cigarettes) {
            $over_allowance_show_popup = 'yes';
        }else{
            $over_allowance_show_popup = 'no';
        }

        $response = array('status' => 'success', 'message' => '', 'over_allowance_show_popup' => $over_allowance_show_popup);
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);
        return $resultJson;       
	}

}
