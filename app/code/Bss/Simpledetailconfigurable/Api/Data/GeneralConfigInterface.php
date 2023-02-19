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
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Api\Data;

interface GeneralConfigInterface
{
    const ENABLE_MODULE = 'enable_module_on_product';
    const ENABLE_AJAX = 'enable_ajax_on_product';

    /**
     * @return bool
     */
    public function getEnableModuleOnProduct();

    /**
     * @param bool $status
     * @return $this
     */
    public function setEnableModuleOnProduct($status);

    /**
     * @return bool
     */
    public function getEnableAjaxOnProduct();

    /**
     * @param bool $status
     * @return $this
     */
    public function setEnableAjaxOnProduct($status);
}
