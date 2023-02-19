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

interface MetaDataInterface
{
    /**
     * Const
     */
    const META_DESCRIPTION = 'meta_description';
    const META_KEYWORD = 'meta_keyword';
    const META_TITLE = 'meta_title';

    /**
     * @return string
     */
    public function getMetaDescription();

    /**
     * @param string $description
     * @return $this
     */
    public function setMetaDescription($description);

    /**
     * @return string
     */
    public function getMetaKeyword();

    /**
     * @param string $keyword
     * @return $this
     */
    public function setMetaKeyword($keyword);

    /**
     * @return string
     */
    public function getMetaTitle();

    /**
     * @param string $title
     * @return $this
     */
    public function setMetaTitle($title);
}
