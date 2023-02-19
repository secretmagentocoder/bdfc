<?php

namespace Amasty\Orderattr\Api\Data;

interface CheckoutEntityInterface
{
    /**#@+
     * Values for parent_entity_type
     */
    const ENTITY_TYPE_ORDER = 1;
    const ENTITY_TYPE_QUOTE = 2;
    /**#@-*/

    /**#@+
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';
    const PARENT_ID = 'parent_id';
    const PARENT_ENTITY_TYPE = 'parent_entity_type';
    /**#@-*/

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutEntityInterface
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getParentId();

    /**
     * @param int $parentId
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutEntityInterface
     */
    public function setParentId($parentId);

    /**
     * @return int
     */
    public function getParentEntityType();

    /**
     * @param int $parentEntityType
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutEntityInterface
     */
    public function setParentEntityType($parentEntityType);
}
