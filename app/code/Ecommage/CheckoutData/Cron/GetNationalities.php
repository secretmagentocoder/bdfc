<?php

namespace Ecommage\CheckoutData\Cron;

use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class GetNationalities
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    const PATH_API_SCRET_KEY_NATIONALITY = 'ecommage_api_nationality/general/key';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\Serializer\Json $json,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->resource                  = $resourceConnection;
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->logger = $logger;
    }

    public function execute()
    {
        $urlApi = 'https://rest.gadventures.com/nationalities?max_per_page=50&page=';
        $baseUrlApi = 'https://rest.gadventures.com/nationalities';
        $callApi = $this->callApi($baseUrlApi);
        $countPage = number_format(round($callApi['count'] / $callApi['max_per_page']));
        $i = 1;
        while ($i <= $countPage) {
            $nationality = $this->callApi($urlApi . $i);
            $this->setNationality($this->covertData($nationality['results']));
            $i++;
        }
    }

    public function callApi($url)
    {
        $scretkey = $this->getScretkeyApi();
        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    sprintf('X-Application-Key: %s', $scretkey),
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return $this->json->unserialize($response);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return  $this;
    }

    public function setNationality($data)
    {
        try {
            $connection = $this->resource->getConnection();
            foreach ($data as $item) {
                $select = $connection->select()
                    ->from(
                        'ecommage_nationalities',
                        ['*']
                    )->where('country_id = ?', $item['country_id']);
                $country = $connection->fetchRow($select);
                if (!$country) {
                    $connection->insert('ecommage_nationalities', $item);
                }

                if ($country != false) {
                    if ($country['country_id'] == $item['country_id'] && $country['name'] != $item['name']) {
                        $connection->update(
                            'ecommage_nationalities',
                            [
                                'name' => $item['name'],
                                'country_id' => $item['country_id'],
                                'country_name' => $item['country_name']
                            ],
                            [
                                'id = ?' => (int)$country['id'],
                            ]
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }


    public function covertData($data)
    {
        $arr = array();
        foreach ($data as $item) {
            try {
                if ($item['country'] && $item['country']['id'] && $item['country']['name']) {
                    $arr[]  = [
                        'name' => $item['name'],
                        'country_id' => $item['country']['id'],
                        'country_name' => $item['country']['name'],
                    ];
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $arr;
    }

    public function getScretkeyApi()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::PATH_API_SCRET_KEY_NATIONALITY, $storeScope);
    }
}
