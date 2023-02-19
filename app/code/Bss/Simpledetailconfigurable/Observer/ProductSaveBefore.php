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
namespace Bss\Simpledetailconfigurable\Observer;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\ObserverInterface;
use Bss\Simpledetailconfigurable\Override\Catalog\Model\Product\Visibility;

class ProductSaveBefore implements ObserverInterface
{
    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $helper;

    /**
     * Constructor.
     *
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Replace visibility configurable product.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();

        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $visibility = $product->getVisibility();

            if ($visibility) {
                if ($visibility == Visibility::VISIBILITY_REDIRECT) {
                    $oldVisibility = $product->getOrigData('visibility');
                    if ($this->helper->isModuleEnable()) {
                        $oldVisibility = $oldVisibility == Visibility::VISIBILITY_NOT_VISIBLE
                            ? Visibility::VISIBILITY_BOTH : $oldVisibility;

                        $product->setOnlyDisplayProductPage(1);
                    }

                    $product->setVisibility($oldVisibility);
                } else {
                    $product->setOnlyDisplayProductPage(0);
                }
            } else {
                $product->setOnlyDisplayProductPage(null);
            }
        }
    }
}
