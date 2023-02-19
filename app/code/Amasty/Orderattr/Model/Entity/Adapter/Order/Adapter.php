<?php

namespace Amasty\Orderattr\Model\Entity\Adapter\Order;

class Adapter
{
    /**
     * @var \Magento\Sales\Api\Data\OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * @var \Amasty\Orderattr\Model\Entity\EntityResolver
     */
    private $entityResolver;

    /**
     * @var \Amasty\Orderattr\Model\Entity\Handler\Save
     */
    private $saveHandler;

    /**
     * @var \Amasty\Orderattr\Model\Value\Metadata\FormFactory
     */
    private $metadataFormFactory;

    public function __construct(
        \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory,
        \Amasty\Orderattr\Model\Entity\EntityResolver $entityResolver,
        \Amasty\Orderattr\Model\Entity\Handler\Save $saveHandler,
        \Amasty\Orderattr\Model\Value\Metadata\FormFactory $metadataFormFactory
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->entityResolver = $entityResolver;
        $this->saveHandler = $saveHandler;
        $this->metadataFormFactory = $metadataFormFactory;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param bool                                   $force
     */
    public function addExtensionAttributesToOrder(\Magento\Sales\Api\Data\OrderInterface $order, $force = false)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if (empty($extensionAttributes)) {
            $extensionAttributes = $this->orderExtensionFactory->create();
            $order->setExtensionAttributes($extensionAttributes);
        }
        if (!$force && !empty($extensionAttributes->getAmastyOrderAttributes())) {
            return;
        }

        $entity = $this->entityResolver->getEntityByOrder($order);
        $customAttributes = $entity->getCustomAttributes();

        if (!empty($customAttributes)) {
            $extensionAttributes->setAmastyOrderAttributes($customAttributes);
        }
        $order->setExtensionAttributes($extensionAttributes);
        $this->setOrderData($order, $entity, $extensionAttributes->getAmastyOrderAttributes());
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    public function saveOrderValues(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getAmastyOrderAttributes()) {
            $entity = $this->entityResolver->getEntityByOrder($order);
            $attributes = $extensionAttributes->getAmastyOrderAttributes();
            $entityType = $entity->getParentEntityType();
            $parentId = $entity->getParentId();
            $entityId = $entity->getEntityId();
            $entity->unsetData();
            $entity->setParentEntityType($entityType);
            $entity->setParentId($parentId);
            $entity->setEntityId($entityId);
            $entity->setCustomAttributes($attributes);
            $this->setOrderData($order, $entity, $attributes);
            $this->saveHandler->execute($entity);
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Amasty\Orderattr\Model\Entity\EntityData $entity
     * @param \Magento\Framework\Api\AttributeValue[] $attributes
     */
    private function setOrderData(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Amasty\Orderattr\Model\Entity\EntityData $entity,
        $attributes
    ) {
        if (!is_array($attributes)) {
            return;
        }
        $form = $this->createEntityForm($entity);
        $data = $form->outputData();

        foreach ($attributes as $orderAttribute) {
            $attributeCode = $orderAttribute->getAttributeCode();
            if (!empty($data[$attributeCode])) {
                $order->setData($attributeCode, $data[$attributeCode]);
            }
        }
    }

    /**
     * Return Form instance
     *
     * @param \Amasty\Orderattr\Model\Entity\EntityData $entity
     *
     * @return \Amasty\Orderattr\Model\Value\Metadata\Form
     */
    protected function createEntityForm($entity)
    {
        /** @var \Amasty\Orderattr\Model\Value\Metadata\Form $formProcessor */
        $formProcessor = $this->metadataFormFactory->create();
        $formProcessor->setFormCode('all_attributes')
            ->setEntity($entity)
            ->setInvisibleIgnored(false);

        return $formProcessor;
    }
}
