<?php

namespace Ecommage\ChangeDeliveryDate\Helper;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

/**
 * Class Data
 *
 * @package Ecommage\ChangeDeliveryDate\Helper
 */
class Data extends AbstractHelper
{
    const NAV_INFO      = 'nav/system/';
    const NAV_EMAIL     = 'nav/config_email/';
    const DATA_DELIVERY = 'delivery_data';
    const SENDER_NAME   = 'trans_email/ident_general/name';
    const SENDER_EMAIL  = 'trans_email/ident_general/email';
    const EMAIL_TEMPLATE  = 'nav/config_email/change_delivery_date_successfully';
    
    /**
     * @var null
     */
    protected $deliveryData = null;
    /**
     * @var null
     */
    protected $dataPersistor = null;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * Data constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder      $transportBuilder
     * @param StateInterface        $inlineTranslation
     * @param Context               $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        Context $context
    ) {
        $this->storeManager      = $storeManager;
        $this->transportBuilder  = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        parent::__construct($context);
    }

    /**
     * Retrieve data persistor
     *
     * @return DataPersistorInterface|mixed
     * @deprecated 101.0.0
     */
    protected function getDataPersistor()
    {
        if (null === $this->dataPersistor) {
            $this->dataPersistor = ObjectManager::getInstance()->get(DataPersistorInterface::class);
        }

        return $this->dataPersistor;
    }

    /**
     * @param $data
     *
     * @return $this
     */
    public function setDeliveryPersistor($data)
    {
        $this->getDataPersistor()->set(self::DATA_DELIVERY, $data);
        return $this;
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function getDeliveryPersistor($key)
    {
        if (null === $this->deliveryData) {
            $this->deliveryData = (array)$this->getDataPersistor()->get(self::DATA_DELIVERY);
            $this->getDataPersistor()->clear(self::DATA_DELIVERY);
        }

        if (isset($this->deliveryData[$key])) {
            return (string)$this->deliveryData[$key];
        }

        return '';
    }

    /**
     * @param      $path
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        if (!empty($storeId)) {
            return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        }

        return $this->scopeConfig->getValue($path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    protected function getConfigApi($path)
    {
        return $this->getConfig(self::NAV_INFO . $path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    protected function getConfigEmail($path)
    {
        return $this->getConfig(self::NAV_EMAIL . $path);
    }

    /**
     * @return string
     */
    public function getUserPwd()
    {
        $user = $this->getConfigApi('user');
        $pwd  = $this->getConfigApi('pwd');
        return sprintf('%s:%s', $user, $pwd);
    }

    /**
     * @param $receiptNo
     * @param $mobileNo
     *
     * @return array
     */
    public function changeSCStatusPOSCheck($receiptNo, $mobileNo)
    {
        $url = $this->getConfigApi('api_delivery_date');
        $xml = sprintf('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ecom="urn:microsoft-dynamics-schemas/codeunit/ECommSaleCreation">
    <soapenv:Header/>
    <soapenv:Body>
        <ecom:ChangeSCStatusPOSCheck>
            <ecom:receiptNo>%s</ecom:receiptNo>
            <ecom:mobileNo>%s</ecom:mobileNo>
        </ecom:ChangeSCStatusPOSCheck>
    </soapenv:Body>
</soapenv:Envelope>', $receiptNo, $mobileNo
        );
        return $this->requestPost($url, $xml);
    }

    /**
     * @param $receiptNo
     * @param $mobileNo
     * @param $newDate
     * @param $newTime
     *
     * @return array
     */
    public function changeSCStatusPOS($receiptNo, $mobileNo, $newDate, $newTime)
    {
        $url = $this->getConfigApi('api_delivery_date');
        $xml = sprintf('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ecom="urn:microsoft-dynamics-schemas/codeunit/ECommSaleCreation">
    <soapenv:Header/>
    <soapenv:Body>
        <ecom:ChangeSCStatusPOS>
            <ecom:receiptNo>%s</ecom:receiptNo>
            <ecom:newDate>%s</ecom:newDate>
            <ecom:newTime>%s</ecom:newTime>
            <ecom:mobileNo>%s</ecom:mobileNo>
        </ecom:ChangeSCStatusPOS>
    </soapenv:Body>
</soapenv:Envelope>', $receiptNo, $newDate, $newTime, $mobileNo
        );
        return $this->requestPost($url, $xml);
    }

    /**
     * @param $uri
     *
     * @return array
     */
    public function requestPost($uri, $body): array
    {
        try {
            $curl = curl_init();
            curl_setopt_array(
                $curl,
                [
                    CURLOPT_URL            => $uri,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING       => '',
                    CURLOPT_MAXREDIRS      => 10,
                    CURLOPT_TIMEOUT        => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTPAUTH       => CURLAUTH_NTLM,
                    CURLOPT_USERPWD        => $this->getUserPwd(),
                    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST  => 'POST',
                    CURLOPT_POSTFIELDS     => "{$body}",
                    CURLOPT_HTTPHEADER     => [
                        'SOAPAction: ""',
                        'Content-Type: application/xml'
                    ],
                ]
            );
            $xmlStr = curl_exec($curl);
            curl_close($curl);
            $message = $this->getTextBetweenTags($xmlStr, 'return_value');
            if (!$message) {
                $this->log(sprintf("API (%s) \nBODY SUBMIT: -------- \n%s \nRESPONSE: -------- \n%s", $uri, $body, $xmlStr));
                throw new Exception("No return results found");
            }
        

            return [
                'status'  => 'success',
                'message' => $this->getTextBetweenTags($xmlStr, 'return_value')
            ];

        } catch (Exception $exception) {
            return [
                'status'  => 'failed',
                'message' => $exception->getMessage()
            ];
        }
    }

    /**
     * @param $string
     * @param $tagname
     *
     * @return string|null
     */
    public function getTextBetweenTags($string, $tagname)
    {
        $pattern = "/<$tagname ?.*>(.*)<\/$tagname>/";
        preg_match($pattern, $string, $matches);
        if (!empty($matches)) {
             $matches = explode(',',$matches[1]);
            return $matches;
        }

        return null;
    }

    /**
     * @param $sendEmail
     * @param $templateVars
     *
     * @return $this
     */
    public function sendEmail(array $templateVars = [],$types = null)
    {
        $templateId =  $this->scopeConfig->getValue(self::EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE);
        if (empty($templateVars)) {
            return $this;
        }
        try {
            $sendEmail = $templateVars['customer_email'];
            $emails = (array)$sendEmail;
            if (strpos($sendEmail, ',') !== false) {
                $emails = explode(',', $sendEmail);
            }
            $toEmail      = array_shift($emails);
            $storeId      = $this->storeManager->getStore()->getId();
            $fromEmail    = $this->getConfig(self::SENDER_EMAIL, $storeId);
            $fromName     = $this->getConfig(self::SENDER_NAME, $storeId);
            $from         = ['email' => $fromEmail, 'name' => $fromName];
            $templateVars = array_merge(
                $templateVars,
                [
                    'sender_email' => $fromEmail,
                    'sender_name'  => $fromName
                ]
            );
            $this->inlineTranslation->suspend();
            $templateOptions  = [
                'area'  => Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $transportBuilder = $this->transportBuilder->setTemplateIdentifier($templateId)
                                                       ->setTemplateOptions($templateOptions)
                                                       ->setTemplateVars($templateVars)
                                                       ->addTo($toEmail)
                                                       ->setFrom($from);
            $transport = $transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (Exception $e) {

            $this->_logger->info($e->getMessage());
        }

        return $this;
    }

    /**
     * @param $msg
     *
     * @return $this
     * @throws Zend_Log_Exception
     */
    public function log($msg)
    {
        $fileName = sprintf('/var/log/e_comm_sale_creation_%s.log', date('Ymd'));
        $writer   = new Zend_Log_Writer_Stream(BP . $fileName);
        $logger   = new Zend_Log();
        $logger->addWriter($writer);
        $logger->info($msg);
        return $this;
    }
}
