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
namespace Bss\Simpledetailconfigurable\Plugin;

class ConfigurableCart
{
    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    private $helper;

    /**
     * ConfigurableCart constructor.
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable $subject
     * @param $result
     */
    public function afterGetProductName(
        \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable $subject,
        $result
    ) {
        if ($this->helper->isShowName()) {
            return $subject->getChildProduct()->getName();
        }
        return $result;
    }
}
