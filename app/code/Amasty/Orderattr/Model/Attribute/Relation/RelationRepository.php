<?php

namespace Amasty\Orderattr\Model\Attribute\Relation;

use Amasty\Orderattr\Api\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\ValidatorException;

class RelationRepository implements \Amasty\Orderattr\Api\RelationRepositoryInterface
{
    /**
     * @var \Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\Relation
     */
    protected $relationResource;

    /**
     * @var RelationFactory
     */
    protected $relationFactory;

    /**
     * @var array
     */
    protected $relations = [];

    /**
     * @var \Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\RelationDetails
     */
    private $detailResource;

    /**
     * RelationRepository constructor.
     * @param \Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\Relation $relationResource
     * @param \Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\RelationDetails $detailResource
     * @param RelationFactory $relationFactory
     */
    public function __construct(
        \Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\Relation $relationResource,
        \Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\RelationDetails $detailResource,
        \Amasty\Orderattr\Model\Attribute\Relation\RelationFactory $relationFactory
    ) {
        $this->relationResource = $relationResource;
        $this->relationFactory = $relationFactory;
        $this->detailResource = $detailResource;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\RelationInterface $relation)
    {
        if ($relation->getRelationId()) {
            $relation = $this->get($relation->getRelationId())->addData($relation->getData());
        }
        try {
            $this->relationResource->save($relation);
            unset($this->relations[$relation->getId()]);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save relation %1', $relation->getRelationId()));
        }
        return $relation;
    }

    /**
     * {@inheritdoc}
     */
    public function get($relationId)
    {
        if (!isset($this->relations[$relationId])) {
            $relation = $this->relationFactory->create();
            $this->relationResource->load($relation, $relationId);
            if (!$relation->getRelationId()) {
                throw new NoSuchEntityException(__('Relation with specified ID "%1" not found.', $relationId));
            }
            $this->relations[$relationId] = $relation;
        }
        return $this->relations[$relationId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\RelationInterface $relation)
    {
        try {
            $this->detailResource->deleteAllDetailForRelation($relation->getRelationId());
            $this->relationResource->delete($relation);
            unset($this->relations[$relation->getId()]);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to remove relation %1', $relation->getRelationId()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($relationId)
    {
        $model = $this->get($relationId);
        $this->delete($model);
        return true;
    }
}
