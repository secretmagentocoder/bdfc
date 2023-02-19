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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Plugin\Catalog\Controller\Product;

class View
{
    const CACHE_INSTANCE_USED_PRODUCT_ATTRIBUTES = '_cache_instance_used_product_attributes';

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $helper;

    /**
     * View construct.
     *
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
     */
    public function __construct(
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->productHelper =$productHelper;
        $this->configurable = $configurable;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
    }

    /**
     * Product view action
     *
     * @param \Magento\Catalog\Controller\Product\View $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundExecute(
        \Magento\Catalog\Controller\Product\View $subject,
        \Closure $proceed
    ) {
        if (!$this->helper->isModuleEnable()) {
            return $proceed();
        }

        try {
            $productId = (int)$subject->getRequest()->getParam('id');
            $productChild = $this->productRepository->getById($productId);

            if ($productChild->getRedirectToConfigurableProduct()) {
                $parentIds = $this->configurable->getParentIdsByChild($productId);
                $parentId = array_shift($parentIds);

                if ($parentId) {
                    $productParentId = (int)$parentId;
                    $product = $this->productRepository->getById($productParentId);

                    if ($product) {
                        return $this->setRedirectUrl($product, $productChild);
                    }
                }
            }

            return $proceed();
        } catch (\Exception $e) {
            return $proceed();
        }
    }

    /**
     * Set url before redirect product.
     *
     * @param \Magento\Catalog\Model\Product|mixed $product
     * @param \Magento\Catalog\Model\Product|mixed $productChild
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function setRedirectUrl($product, $productChild)
    {
        $optionAttr = $product->getData(self::CACHE_INSTANCE_USED_PRODUCT_ATTRIBUTES);

        $paramRedirect = '?+';
        foreach ($optionAttr as $attr) {
            $attrCode = $attr->getAttributeCode();
            $paramRedirect .= $attrCode . '-' . $productChild->getAttributeText($attrCode) . '+';
        }
        $paramRedirect .= 'sdcp-redirect';

        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $product->getProductUrl() . $paramRedirect;
        $resultRedirect->setUrl($url);

        return $resultRedirect;
    }
}
