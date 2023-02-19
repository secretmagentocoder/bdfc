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
 * @package    Bss_MinMaxQtyOrderPerCate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MinMaxQtyOrderPerCate\Plugin\Block;

class Cart
{
    /**
     * Helper
     *
     * @var \Bss\MinMaxQtyOrderPerCate\Helper\Data
     */
    protected $minmaxHelper;

    /**
     * Construct
     *
     * @param \Bss\MinMaxQtyOrderPerCate\Helper\Data $minmaxHelper
     */
    public function __construct(
        \Bss\MinMaxQtyOrderPerCate\Helper\Data $minmaxHelper
    ) {
        $this->minmaxHelper = $minmaxHelper;
    }

    /**
     * @param \Magento\Checkout\Block\Cart\AbstractCart $subject
     * @param $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetItemRenderer(\Magento\Checkout\Block\Cart\AbstractCart $subject, $result)
    {
        if ($this->minmaxHelper->getConfig('enable')) {
            if ($this->minmaxHelper->getConfig('show_category')) {
                $result->setTemplate('Bss_MinMaxQtyOrderPerCate::cart/item/default.phtml');
            }
        }
        return $result;
    }
}
