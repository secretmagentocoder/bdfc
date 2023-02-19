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
use Bss\Simpledetailconfigurable\Api\Data\ImageDataInterface;

class ImageData extends DataObject implements ImageDataInterface
{
    /**
     * @inheritDoc
     */
    public function getCaption()
    {
        return $this->getData(self::CAPTION);
    }

    /**
     * @inheritDoc
     */
    public function setCaption($caption)
    {
        return $this->setData(self::CAPTION, $caption);
    }

    /**
     * @inheritDoc
     */
    public function getFull()
    {
        return $this->getData(self::FULL);
    }

    /**
     * @inheritDoc
     */
    public function setFull($linkFull)
    {
        return $this->setData(self::FULL, $linkFull);
    }

    /**
     * @inheritDoc
     */
    public function getImg()
    {
        return $this->getData(self::IMG);
    }

    /**
     * @inheritDoc
     */
    public function setImg($linkImg)
    {
        return $this->setData(self::IMG, $linkImg);
    }

    /**
     * @inheritDoc
     */
    public function getIsMain()
    {
        return $this->getData(self::IS_MAIN);
    }

    /**
     * @inheritDoc
     */
    public function setIsMain($isMain)
    {
        return $this->setData(self::IS_MAIN, $isMain);
    }

    /**
     * @inheritDoc
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * @inheritDoc
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * @inheritDoc
     */
    public function getThumb()
    {
        return $this->getData(self::THUMB);
    }

    /**
     * @inheritDoc
     */
    public function setThumb($linkThumb)
    {
        return $this->setData(self::THUMB, $linkThumb);
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritDoc
     */
    public function getVideoUrl()
    {
        return $this->getData(self::VIDEO_URL);
    }

    /**
     * @inheritDoc
     */
    public function setVideoUrl($url)
    {
        return $this->setData(self::VIDEO_URL, $url);
    }
}
