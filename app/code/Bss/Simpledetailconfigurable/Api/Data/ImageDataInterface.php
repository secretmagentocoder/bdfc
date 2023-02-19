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

interface ImageDataInterface
{
    /**
     * Const
     */
    const CAPTION = 'caption';
    const FULL = 'full';
    const IMG = 'img';
    const IS_MAIN = 'is_main';
    const POSITION = 'position';
    const THUMB = 'thumb';
    const TYPE = 'type';
    const VIDEO_URL = 'video_url';

    /**
     * @return string
     */
    public function getCaption();

    /**
     * @param string $caption
     * @return $this
     */
    public function setCaption($caption);

    /**
     * @return string
     */
    public function getFull();

    /**
     * @param string $linkFull
     * @return $this
     */
    public function setFull($linkFull);

    /**
     * @return string
     */
    public function getImg();

    /**
     * @param string $linkImg
     * @return $this
     */
    public function setImg($linkImg);

    /**
     * @return bool
     */
    public function getIsMain();

    /**
     * @param bool $isMain
     * @return $this
     */
    public function setIsMain($isMain);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * @return string
     */
    public function getThumb();

    /**
     * @param string $linkThumb
     * @return $this
     */
    public function setThumb($linkThumb);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getVideoUrl();

    /**
     * @param string $url
     * @return $this
     */
    public function setVideoUrl($url);
}
