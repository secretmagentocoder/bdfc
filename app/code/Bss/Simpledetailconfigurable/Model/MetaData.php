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
use Bss\Simpledetailconfigurable\Api\Data\MetaDataInterface;

class MetaData extends DataObject implements MetaDataInterface
{
    /**
     * @inheritDoc
     */
    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setMetaDescription($description)
    {
        return $this->setData(self::META_DESCRIPTION, $description);
    }

    /**
     * @inheritDoc
     */
    public function getMetaKeyword()
    {
        return $this->getData(self::META_KEYWORD);
    }

    /**
     * @inheritDoc
     */
    public function setMetaKeyword($keyword)
    {
        return $this->setData(self::META_KEYWORD, $keyword);
    }

    /**
     * @inheritDoc
     */
    public function getMetaTitle()
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setMetaTitle($title)
    {
        return $this->setData(self::META_TITLE, $title);
    }
}
