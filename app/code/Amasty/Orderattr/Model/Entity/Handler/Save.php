<?php

namespace Amasty\Orderattr\Model\Entity\Handler;

use Amasty\Orderattr\Model\ResourceModel\Entity\Entity as EntityResource;
use Amasty\Orderattr\Api\Data\EntityDataInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class Save
{
    /**
     * @var EntityResource
     */
    private $entityResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityResource $entityResource, LoggerInterface $logger)
    {
        $this->entityResource = $entityResource;
        $this->logger = $logger;
    }

    /**
     * @param EntityDataInterface|\Amasty\Orderattr\Model\Entity\EntityData $entityData
     *
     * @return EntityDataInterface
     * @throws CouldNotSaveException
     */
    public function execute(EntityDataInterface $entityData)
    {
        try {
            if (!$entityData->getEntityId()) {
                $entityData->setEntityId($this->entityResource->reserveEntityId());
            }

            $this->entityResource->save($entityData);
        } catch (\Exception $e) {
            $this->logger->critical('Unable to save Amasty Order Attributes', ['exception' => $e->getMessage()]);
            throw new CouldNotSaveException(__('Unable to save Order Attributes. Error: %1', $e->getMessage()));
        }

        return $entityData;
    }
}
