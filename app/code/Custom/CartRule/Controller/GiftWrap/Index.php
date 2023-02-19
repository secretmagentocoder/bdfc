<?php

namespace Custom\CartRule\Controller\GiftWrap;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

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

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/gift_wrap.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $logger->info('Observer Start --------------------------------');

        $quoteId = @$_POST['cart_item_id'];
        $is_gift_wrap = @$_POST['is_gift_wrap'];

        if (!empty($quoteId)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');

            $gift_wrap_id = null;
            if ($is_gift_wrap == 'true') {
                $gift_wrap_id = 1;
                $query = "UPDATE `quote_item` SET `gw_id` = '1' WHERE `quote_item`.`item_id` = $quoteId";
                $connection->query($query);
            }else{
                $gift_wrap_id = null;
                $query = "UPDATE `quote_item` SET `gw_id` = NULL WHERE `quote_item`.`item_id` = $quoteId";
                $connection->query($query);
            }

            // $query = "UPDATE `quote_item` SET `gw_id` = $gift_wrap_id WHERE `quote_item`.`item_id` = $quoteId; ";
            // $results = $connection->query($query);

            $response = array('status' => 'success', 'message' => 'Gift wrap updated successfully.');
        }else{
            $response = array('status' => 'error', 'message' => 'Please select quote item id.');
        }
        echo json_encode($response);
	}

}