<?php

namespace Custom\Api\Controller\Sms;

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
        array $data = array()
        )
    {
        $resultJsonFactory = $resultJsonFactory;
        $resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/api_sms.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $response = array();
        // $user="bdutyfree";
        // $password="23491656";
        $mobilenumbers=$_GET['mobilenumber'];
        $message=$_GET['message'];
        // $message = "New Order";
        // $senderid="BDFC";
        // $messagetype="N";
        // $DReports="Y";
        $url="http://api.smscountry.com/SMSCwebservice_bulk.aspx?User=bdutyfree&passwd=23491656&sid=BDFC&mtype=N&DR=Y";
        // $url="http://www.smscountry.com/SMSCwebservice_Bulk.aspx";
        $message = urlencode($message);
        $ch = curl_init();
        $ret = curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, "mobilenumber=$mobilenumbers&message=$message");
        // curl_setopt ($ch, CURLOPT_POSTFIELDS, "User=$user&passwd=$password&mobilenumber=$mobilenumbers&message=$message&sid=$senderid&mtype=$messagetype&DR=$DReports");
        $ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $curlresponse = curl_exec($ch);
        curl_close($ch);
        print_r($curlresponse);
	}
}