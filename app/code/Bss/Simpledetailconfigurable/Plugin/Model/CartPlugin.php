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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Plugin\Model;

class CartPlugin
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurableType;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerialize;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * CartPlugin constructor.
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerialize
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerialize,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->configurableType = $configurableType;
        $this->moduleConfig = $moduleConfig;
        $this->checkoutSession = $checkoutSession;
        $this->jsonSerialize = $jsonSerialize;
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Checkout\Model\Cart $cart
     * @param $result
     * @param $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return mixed
     */
    public function afterAddProduct(
        \Magento\Checkout\Model\Cart $cart,
        $result,
        $productInfo,
        $requestInfo = null
    ) {
        $this->setChildProductSession($requestInfo);
        return $result;
    }

    /**
     * @param \Magento\Checkout\Model\Cart $cart
     * @param $result
     * @param $itemId
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @param null $updatingParams
     * @return mixed
     */
    public function afterUpdateItem(
        \Magento\Checkout\Model\Cart $cart,
        $result,
        $itemId,
        $requestInfo = null,
        $updatingParams = null
    ) {
        $this->setChildProductSession($requestInfo);
        return $result;
    }

    /**
     * @param $requestInfo
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function setChildProductSession($requestInfo)
    {
        $lastAddedProductId = $this->checkoutSession->getLastAddedProductId();
        try {
            $product = $this->productRepository->getById($lastAddedProductId);
        } catch (\Exception $exception) {
            $product = null;
        }
        if ($this->moduleConfig->isModuleEnable() &&
            $requestInfo !== null &&
            $product && $product->getTypeId() === 'configurable') {
            $superAttribute = [];
            if (is_string($requestInfo)) {
                $requestInfoArr = $this->jsonSerialize->serialize($requestInfo);
                $superAttribute = $requestInfoArr['super_attribute'] ?? [];
            } elseif (is_object($requestInfo) && $requestInfo instanceof \Magento\Framework\DataObject) {
                $superAttribute = $requestInfo->getData('super_attribute');
            } elseif (is_array($requestInfo)) {
                $superAttribute = $requestInfo['super_attribute'] ?? [];
            }
            $lastChildAdded = $this->configurableType->getProductByAttributes($superAttribute, $product);
            if ($lastChildAdded->getId()) {
                $this->checkoutSession->setLastAddedChildProductId($lastChildAdded->getId());
            }
        }
    }
}
