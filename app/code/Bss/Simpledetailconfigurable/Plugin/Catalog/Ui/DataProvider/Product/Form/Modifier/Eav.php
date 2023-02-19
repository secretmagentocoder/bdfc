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
namespace Bss\Simpledetailconfigurable\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Bss\Simpledetailconfigurable\Override\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Eav
{
    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|null
     */
    protected $product;

    /**
     * @var LocatorInterface
     * @since 101.0.0
     */
    protected $locator;

    /**
     * @var Configurable
     */
    protected $configurableProduct;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $helper;

    /**
     * Eav constructor.
     *
     * @param LocatorInterface $locator
     * @param Configurable $configurableProduct
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
     */
    public function __construct(
        LocatorInterface $locator,
        Configurable $configurableProduct,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
    ) {
        $this->locator = $locator;
        $this->configurableProduct = $configurableProduct;
        $this->helper = $helper;
    }

    /**
     * Replace visibility configurable product.
     *
     * @param \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav $subject
     * @param array|mixed $result
     * @return array|mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterModifyData(
        \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav $subject,
        $result
    ) {
        if (!$this->helper->isModuleEnable()) {
            return $result;
        }

        try {
            if (!$this->product) {
                $this->product = $this->locator->getProduct();
            }
            $productId = $this->product->getId();

            if (isset($result[$productId]['product']['only_display_product_page'])) {
                $onlyDisplayProductPage = $result[$productId]['product']['only_display_product_page'];
                if ($onlyDisplayProductPage) {
                    $result[$productId]['product']['visibility'] = Visibility::VISIBILITY_REDIRECT;
                }
            }

            return $result;
        } catch (\Exception $e) {
            return $result;
        }
    }

    /**
     * Replace visibility configurable product.
     *
     * @param \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterModifyMeta(
        \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav $subject,
        $result
    ) {
        if (!$this->helper->isModuleEnable()) {
            if (isset($result['sdcp-redirect'])) {
                $result['sdcp-redirect']['arguments']['data']['config']['visible'] = false;
            }
            return $result;
        }

        try {
            if (!$this->product) {
                $this->product = $this->locator->getProduct();
            }
            $productId = $this->product->getId();
            $productType = $this->product->getTypeId();

            if ($productType !== Configurable::TYPE_CODE) {
                $parentId = $this->configurableProduct->getParentIdsByChild($productId);
                if (!$parentId && isset($result['sdcp-redirect'])) {
                    $result['sdcp-redirect']['arguments']['data']['config']['visible'] = false;
                }

                //Remove:visibility new option 'only_display_product_page' in group general.
                if (isset($result['product-details']['children']['container_visibility']['children']['visibility'])) {
                    $result['product-details']['children']['container_visibility']['children']['visibility']
                    ['arguments']['data']['config']['options'][4] = false;
                }
            } else {
                if (isset($result['sdcp-redirect-hidden'])) {
                    $result['sdcp-redirect-hidden']['arguments']['data']['config']['visible'] = false;
                }
            }

            return $result;
        } catch (\Exception $e) {
            return $result;
        }
    }
}
