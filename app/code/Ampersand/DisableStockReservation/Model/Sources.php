<?php

namespace Ampersand\DisableStockReservation\Model;

use Magento\Framework\Model\AbstractModel;
use Ampersand\DisableStockReservation\Api\Data\SourcesInterface;
use Ampersand\DisableStockReservation\Model\ResourceModel\Sources as ResourceModelSources;

/**
 * Class Sources
 * @package Ampersand\DisableStockReservation\Model
 */
class Sources extends AbstractModel implements SourcesInterface
{
    public function _construct()
    {
        $this->_init(ResourceModelSources::class);
    }

    /**
     * @return null|string
     */
    public function getSources(): ?string
    {
        return $this->getData(SourcesInterface::SOURCES_KEY);
    }

    /**
     * @param string $sources
     * @return SourcesInterface
     */
    public function setSources(string $sources): SourcesInterface
    {
        return $this->setData(SourcesInterface::SOURCES_KEY, $sources);
    }

    /**
     * @return string|null
     */
    public function getOrderId(): ?string
    {
        return $this->getData(SourcesInterface::ORDER_ID_KEY);
    }

    /**
     * @param string $id
     * @return SourcesInterface
     */
    public function setOrderId(string $id): SourcesInterface
    {
        return $this->setData(SourcesInterface::ORDER_ID_KEY, $id);
    }
}
