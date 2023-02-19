<?php

namespace Amasty\Orderattr\Block\Order;

use Amasty\Orderattr\Model\Value\Metadata\Form;

class Email extends Attributes
{
    /**
     * Return Checkout Form instance
     *
     * @param \Amasty\Orderattr\Model\Entity\EntityData $entity
     * @return Form
     */
    protected function createEntityForm($entity)
    {
        /** @var Form $formProcessor */
        $formProcessor = $this->metadataFormFactory->create();
        $formProcessor->setFormCode('frontend_order_email')
            ->setEntity($entity)
            ->setStore($this->getOrder()->getStore());

        return $formProcessor;
    }
}
