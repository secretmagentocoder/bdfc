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
namespace Bss\Simpledetailconfigurable\Model;

use Magento\Framework\DataObject;
use Bss\Simpledetailconfigurable\Api\Data\GeneralConfigInterface;

class GeneralConfig extends DataObject implements GeneralConfigInterface
{
    /**
     * @inheritDoc
     */
    public function getEnableModuleOnProduct()
    {
        return $this->getData(self::ENABLE_MODULE);
    }

    /**
     * @inheritDoc
     */
    public function setEnableModuleOnProduct($status)
    {
        return $this->setData(self::ENABLE_MODULE, $status);
    }

    /**
     * @inheritDoc
     */
    public function getEnableAjaxOnProduct()
    {
        return $this->getData(self::ENABLE_AJAX);
    }

    /**
     * @inheritDoc
     */
    public function setEnableAjaxOnProduct($status)
    {
        return $this->setData(self::ENABLE_AJAX, $status);
    }
}
