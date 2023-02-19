<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Plugin\Product\Helper;

use Bss\Simpledetailconfigurable\Helper\ModuleConfig;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Configurable
{
    /**
     * @var ProductAttributeRepositoryInterface|null
     */
    protected $productAttributeRepository;
    /**
     * @var SearchCriteriaBuilder|null
     */
    protected $searchCriteriaBuilder;
    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;
    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerialize;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Configurable constructor.
     * @param ModuleConfig $moduleConfig
     * @param ProductAttributeRepositoryInterface|null $productAttributeRepository
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerialize
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig,
        ProductAttributeRepositoryInterface $productAttributeRepository = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerialize,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productMetadata = $productMetadata;
        $this->catalogConfig = $catalogConfig;
        $this->cart = $cart;
        $this->jsonSerialize = $jsonSerialize;
        $this->request = $request;
    }

    /**
     * @param $subject
     * @param $result
     * @return $this
     */
    public function afterStart($subject, $result)
    {
        return $this;
    }

    /**
     * @return ModuleConfig
     */
    public function getModuleConfig()
    {
        return $this->moduleConfig;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \Magento\Checkout\Model\Cart
     */
    public function getCartModel()
    {
        return $this->cart;
    }

    /**
     * @return \Magento\Framework\Serialize\Serializer\Json
     */
    public function getJsonSerialize()
    {
        return $this->jsonSerialize;
    }

    /**
     * @return SearchCriteriaBuilder|null
     */
    public function getSearchCriteriaBuilder()
    {
        return $this->searchCriteriaBuilder;
    }

    /**
     * @return ProductAttributeRepositoryInterface|null
     */
    public function getProductAttributeRepository()
    {
        return $this->productAttributeRepository;
    }

    /**
     * @return \Magento\Catalog\Model\Config
     */
    public function getDataCatalogConfig()
    {
        return $this->catalogConfig;
    }
}
