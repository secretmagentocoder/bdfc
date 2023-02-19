<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */
declare(strict_types=1);

namespace MageWorx\OptionBase\Model\ResourceModel\Product\Option\Value\Collection;

use Magento\Framework\DB\Select;
use Magento\Framework\Serialize\SerializerInterface;
use MageWorx\OptionBase\Model\ResourceModel\CollectionUpdaterRegistry;
use MageWorx\OptionBase\Model\GeneralConfigReader;

/**
 * Retrieve collection data from cache, fail over to another fetch strategy, if cache does not exist yet
 */
class CacheFetchStrategy implements \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
{
    const CACHE_TAG_PREFIX = 'mw_p_o_v';

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
     */
    protected $fetchStrategy;

    /**
     * @var string
     */
    protected $cacheIdPrefix = '';

    /**
     * @var array
     */
    protected $cacheTags = [];

    /**
     * @var int|bool|null
     */
    protected $cacheLifetime = null;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var GeneralConfigReader
     */
    protected $configReader;

    /**
     * @var CollectionUpdaterRegistry
     */
    protected $collectionUpdaterRegistry;

    /**
     * @param CollectionUpdaterRegistry $collectionUpdaterRegistry
     * @param \Magento\Framework\App\Cache\Type\Collection $cache
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param SerializerInterface $serializer
     * @param GeneralConfigReader $configReader
     * @param string $cacheIdPrefix
     * @param array $cacheTags
     * @param int|bool|null $cacheLifetime
     */
    public function __construct(
        CollectionUpdaterRegistry $collectionUpdaterRegistry,
        \Magento\Framework\App\Cache\Type\Collection $cache,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        SerializerInterface $serializer,
        GeneralConfigReader $configReader,
        string $cacheIdPrefix = '',
        array $cacheTags = [],
        $cacheLifetime = null
    ) {
        $this->collectionUpdaterRegistry = $collectionUpdaterRegistry;
        $this->cache                     = $cache;
        $this->fetchStrategy             = $fetchStrategy;
        $this->serializer                = $serializer;
        $this->configReader              = $configReader;
        $this->cacheIdPrefix             = $cacheIdPrefix;
        $this->cacheTags                 = $cacheTags;
        $this->cacheLifetime             = $cacheLifetime;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll(Select $select, array $bindParams = [])
    {
        if (!$this->configReader->useOptionValueCollectionCache()) {
            return $this->fetchStrategy->fetchAll($select, $bindParams);
        }

        $entityIds  = $this->collectionUpdaterRegistry->getCurrentEntityIds();
        $entityType = $this->collectionUpdaterRegistry->getCurrentEntityType();

        if (count($entityIds) == 1 && $entityType == 'product') {
            $this->cacheTags[] = self::CACHE_TAG_PREFIX . '_' . reset($entityIds);
            $cacheId           = $this->getSelectCacheId($select, $bindParams);
            $result            = $this->cache->load($cacheId);

            if ($result) {
                $result = $this->serializer->unserialize($result);
            } else {
                $result = $this->fetchStrategy->fetchAll($select, $bindParams);
                $this->cache->save(
                    $this->serializer->serialize($result),
                    $cacheId,
                    $this->cacheTags,
                    $this->cacheLifetime
                );
            }

            return $result;
        }

        return $this->fetchStrategy->fetchAll($select, $bindParams);
    }

    /**
     * Determine cache identifier based on select query
     *
     * @param Select $select
     * @param array $bindParams
     * @return string
     */
    protected function getSelectCacheId(Select $select, array $bindParams): string
    {
        return $this->cacheIdPrefix . hash('md5', (string)$select . $this->serializer->serialize($bindParams));
    }
}
