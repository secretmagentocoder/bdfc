<?php declare(strict_types=1);
/**
 * @package Ceymox_Navconnector
 */
namespace Ceymox\Navconnector\Model\Curl;

use Magento\Framework\HTTP\Client\Curl;
use Ceymox\Navconnector\Model\ConfigProvider;
use Psr\Log\LoggerInterface;

class CurlOperations
{
    /**
     * @var Curl
     */
    protected $curl;
    /**
     * @var ConfigProvider
     */
    protected $navConfigProvider;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Data constructor.
     *
     * @param Curl $curl
     * @param ConfigProvider $navConfigProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        Curl $curl,
        ConfigProvider $navConfigProvider,
        LoggerInterface $logger
    ) {
        $this->curl = $curl;
        $this->navConfigProvider = $navConfigProvider;
        $this->logger = $logger;
    }

    /**
     * Curl request
     *
     * @param string $endpoint
     * @param array $filter
     * @return array
     */
    public function makeRequest($endpoint, $filter = null)
    {
        try {

            $url = $this->getEndpoint($endpoint, $filter);
            $userName = $this->navConfigProvider->getUser();
            $password = $this->navConfigProvider->getPassword();
            $options = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
                CURLOPT_USERPWD => $userName.':'.$password,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
            ];
            $this->curl->setOptions($options);
            $this->curl->get($url);
            $headers = ["Content-Type" => "application/json"];
            $this->curl->setHeaders($headers);
            $this->curl->get($url);
            $response = $this->curl->getBody();
            $requestStatus = $this->curl->getStatus();
            $data = json_decode($response, true);
            return $data;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->logger->info($message);
            return [];
        }
    }

    /**
     * Get Endpoint
     *
     * @param string $endpoint
     * @param array $filter
     * @param string $storeId
     * @return string
     */
    public function getEndpoint($endpoint, $filter, $storeId = null)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $str = '';
        if (!empty($filter)) {
            $str = '&$filter=';
            foreach ($filter as $key => $val) {
                if ($key == 'Web_Change_Date_Time') {
                    $str .=$key.'%20gt%20%27'.str_replace(' ', '%20', $val).'%27&';
                } else {
                    $str .=$key.'%20eq%20%27'.$val.'%27&';
                }
            }
            $str .='$top=10&$skip=0';
        }
        $url = $host.'/Company(%27'.$company.'%27)/'.$endpoint.'?$format=application/json'.$str;
        return $url;
    }
}
