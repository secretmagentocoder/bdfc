<?php

namespace Amasty\Orderattr\Api;

/**
 * Interface RelationRepositoryInterface
 *
 * @api
 */
interface RelationRepositoryInterface
{
    /**
     * @param \Amasty\Orderattr\Api\Data\RelationInterface $relation
     *
     * @return \Amasty\Orderattr\Api\Data\RelationInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Orderattr\Api\Data\RelationInterface $relation);

    /**
     * @param int $relationId
     *
     * @return \Amasty\Orderattr\Api\Data\RelationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($relationId);

    /**
     * @param \Amasty\Orderattr\Api\Data\RelationInterface $relation
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Orderattr\Api\Data\RelationInterface $relation);

    /**
     * @param int $ruleId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($ruleId);
}
