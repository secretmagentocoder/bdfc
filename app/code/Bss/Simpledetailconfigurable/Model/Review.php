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

use Bss\Simpledetailconfigurable\Api\Data\ReviewInterface;
use Magento\Framework\DataObject;

class Review extends DataObject implements ReviewInterface
{
    /**
     * @inheritDoc
     */
    public function getReviewId()
    {
        return $this->getData(self::REVIEW_ID);
    }

    /**
     * @inheritDoc
     */
    public function setReviewId($reviewId)
    {
        return $this->setData(self::REVIEW_ID, $reviewId);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getStatusId()
    {
        return $this->getData(self::STATUS_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStatusId($statusId)
    {
        return $this->setData(self::STATUS_ID, $statusId);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritDoc
     */
    public function getDetail()
    {
        return $this->getData(self::DETAIL);
    }

    /**
     * @inheritDoc
     */
    public function setDetail($detail)
    {
        return $this->setData(self::DETAIL, $detail);
    }

    /**
     * @inheritDoc
     */
    public function getNickname()
    {
        return $this->getData(self::NICKNAME);
    }

    /**
     * @inheritDoc
     */
    public function setNickname($nickName)
    {
        return $this->setData(self::NICKNAME, $nickName);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }
}
