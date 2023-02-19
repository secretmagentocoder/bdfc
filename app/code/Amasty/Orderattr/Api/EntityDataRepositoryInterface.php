<?php

namespace Amasty\Orderattr\Api;

/**
 * @api
 */
interface EntityDataRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Orderattr\Api\Data\EntityDataInterface $entityData
     * @return \Amasty\Orderattr\Api\Data\EntityDataInterface
     */
    public function save(\Amasty\Orderattr\Api\Data\EntityDataInterface $entityData);

    /**
     * Get by id
     *
     * @param int $entityId
     * @return \Amasty\Orderattr\Api\Data\EntityDataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);

    /**
     * Delete
     *
     * @param \Amasty\Orderattr\Api\Data\EntityDataInterface $entityData
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Orderattr\Api\Data\EntityDataInterface $entityData);

    /**
     * Delete by id
     *
     * @param int $entityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($entityId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
