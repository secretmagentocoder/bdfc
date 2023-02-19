<?php
namespace Bodak\CheckoutCustomForm\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Psr\Log\LoggerInterface;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class Data extends AbstractHelper
{
    private $navConfigProvider;
    private $checkoutDataHelper;

    public function __construct
    (
        \ExperiencesDigital\CustomCalculation\Model\ResourceModel\CustomCalculation\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ecommage\CheckoutData\Helper\Data $checkoutDataHelper,
        LoggerInterface $logger,
        ConfigProvider $navConfigProvider,
        Context $context,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    )
    {
        $this->checkoutDataHelper = $checkoutDataHelper;
        $this->collectionFactory = $collectionFactory;
        $this->_storeManager = $storeManager;
        $this->_logger = $logger;
        $this->navConfigProvider = $navConfigProvider;
        $this->currencyFactory = $currencyFactory;
        parent::__construct($context);
    }

    public function callApi($url)
    {
        try {
            $user = $this->navConfigProvider->getUser();
            $password = $this->navConfigProvider->getPassword();
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
                CURLOPT_USERPWD => sprintf("%s:%s", $user, $password),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ]);
            $response_data = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response_data, true);
            if (is_array($response) && count($response) > 0) {
                return $response['value'];
            } else {
                return [];
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return  $this;
    }

    public function getHading($data)
    {
        $base = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_DIRECT_LINK);
        $storeCode = $this->_storeManager->getStore()->getCode();
        $url = $base."rest/".$storeCode."/V1/experiencesdigital/custom_calcuation";
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['cart' => $data]));
            $resp = curl_exec($curl);
            curl_close($curl);
            return $resp;
        }catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return  $this;
    }

    public function getCustomCategoryCalculation($code)
    {
        if (!empty(strpos($code,'+')) && is_string($code))
        {
            $param = explode('+',$code);
            $code = $param[0];
        }
        return $this->collectionFactory->create()
                                        ->addFieldToFilter('Code',$code)
                                       ->addFieldToFilter('Active',1);
    }

    public function getCustomDuty()
    {
        $customDuty = 0;
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
     
        $data = $this->checkoutDataHelper->curlCall($host.'/Company(%27'.$company.'%27)/WebCompanyInformation?$format=application/json');
        if (! empty($data)) {
            if (isset($data[0])) {
                $customDuty = isset($data[0]['VATPercent_for_Custom_Duty'])? (int) $data[0]['VATPercent_for_Custom_Duty']:0;
            }
        }

        return $customDuty;
    }

    public function convertPriceFromBaseCurrencyToCurrentCurrency($price)
    {
        $currentCurrencyCode =  $this->_storeManager->getStore()
            ->getCurrentCurrency()
            ->getCode();
        $baseCurrencyCode =  $this->_storeManager->getStore()
            ->getBaseCurrency()
            ->getCode();

        if ($baseCurrencyCode != $currentCurrencyCode) {
            $rate = $this->currencyFactory->create()
                ->load($baseCurrencyCode)
                ->getAnyRate($currentCurrencyCode);

            $price = $price * $rate;
        }
        return $price;
    }

}
