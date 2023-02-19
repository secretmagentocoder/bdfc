<?php

namespace Ecommage\ChangeDeliveryDate\Helper;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
//use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class CoutryCodes
 *
 * @package Ecommage\ChangeDeliveryDate\Helper
 */
class CountryCodes extends AbstractHelper
{
    const COUNTRY_CODES_CACHE_TAG = 'COUNTRY_CODES_DATA';
    /**
     * @var File
     */
    private $file;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var null
     */
    protected $countryCodes = null;

    /**
     * CountryCodes constructor.
     *
     * @param SerializerInterface $serializer
     * @param CacheInterface      $cache
     * @param Reader              $moduleReader
     * @param Context             $context
     * @param File                $file
     */
    public function __construct(
        SerializerInterface $serializer,
        CacheInterface $cache,
        Reader $moduleReader,
        Context $context,
        File $file
    ) {
        $this->file         = $file;
        $this->cache        = $cache;
        $this->moduleReader = $moduleReader;
        $this->serializer   = $serializer;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getAllCountryCodes()
    {
        if ($this->countryCodes !== null) {
            return $this->countryCodes;
        }
//        $cacheKey = self::COUNTRY_CODES_CACHE_TAG;
//        dd($cacheKey);
//        $countryCodesJson = $this->cache->load($cacheKey);
//        if (!empty($countryCodesJson)) {
//            $this->countryCodes = $this->serializer->unserialize($countryCodesJson);
//            return $this->countryCodes;
//        }

        $countryCodesJsonFile = $this->moduleReader->getModuleDir(false, 'Magento_TwoFactorAuth') .
                                DIRECTORY_SEPARATOR . 'Setup' .
                                DIRECTORY_SEPARATOR . 'data' .
                                DIRECTORY_SEPARATOR . 'country_codes.json';
        $countryCodesJson = $this->file->fileGetContents($countryCodesJsonFile);


        $countryCodes = $this->serializer->unserialize(trim($countryCodesJson));
        if (!empty($countryCodes)) {
            foreach ($countryCodes as $country) {
                $code = $country['code'];
                $this->countryCodes[$code] = $country;
            }
        }

//        $data = $this->serializer->serialize($this->countryCodes);
////        dd($data);
//        $this->cache->save($data, $cacheKey);
        return $this->countryCodes;
    }

    /**
     * @param $code
     *
     * @return array
     */
    public function getCountryCode($code)
    {
        if ($this->countryCodes === null) {
            $this->getAllCountryCodes();
        }

        return $this->countryCodes[strtoupper($code)] ?? [];
    }

    /**
     * @param $countryId
     * @param $mobileNumber
     *
     * @return string
     */
    public function getFullMobileNo($countryId, $mobileNumber)
    {
        $country = $this->getCountryCode($countryId);
        if (!empty($country)) {
            $dialCode = $country['dial_code'] ?? '';
            if ($dialCode && substr($mobileNumber,0,1) != '+') {
                return $dialCode . $mobileNumber;
            }
        }

        return $mobileNumber;
    }
}
