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

interface ReviewInterface
{
    /**
     * Const
     */
    const REVIEW_ID = 'review_id';
    const CREATED_AT = 'created_at';
    const STATUS_ID = 'status_id';
    const TITLE = 'title';
    const DETAIL = 'detail';
    const NICKNAME = 'nickname';
    const CUSTOMER_ID = 'customer_id';

    /**
     * @return int
     */
    public function getReviewId();

    /**
     * @param int $reviewId
     * @return $this
     */
    public function setReviewId($reviewId);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return int
     */
    public function getStatusId();

    /**
     * @param int $statusId
     * @return $this
     */
    public function setStatusId($statusId);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getDetail();

    /**
     * @param string $detail
     * @return $this
     */
    public function setDetail($detail);

    /**
     * @return string
     */
    public function getNickname();

    /**
     * @param string $nickName
     * @return $this
     */
    public function setNickname($nickName);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);
}
