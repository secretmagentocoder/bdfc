<?php

namespace Amasty\Orderattr\Model\Entity\Adapter\Quote;

use Amasty\Orderattr\Model\Attribute\ForbidValidator;
use Amasty\Orderattr\Model\Entity\EntityResolver;
use Amasty\Orderattr\Model\Entity\Handler\Save;
use Magento\Quote\Api\Data\CartExtensionFactory;

class Adapter
{
    /**
     * @var CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    /**
     * @var Save
     */
    private $saveHandler;

    /**
     * @var ForbidValidator
     */
    private $forbidValidator;

    public function __construct(
        CartExtensionFactory $cartExtensionFactory,
        EntityResolver $entityResolver,
        Save $saveHandler,
        ForbidValidator $forbidValidator
    ) {
        $this->cartExtensionFactory = $cartExtensionFactory;
        $this->entityResolver = $entityResolver;
        $this->saveHandler = $saveHandler;
        $this->forbidValidator = $forbidValidator;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param bool                                  $force
     */
    public function addExtensionAttributesToQuote(\Magento\Quote\Api\Data\CartInterface $quote, $force = false)
    {
        $extensionAttributes = $quote->getExtensionAttributes();
        if (empty($extensionAttributes)) {
            $extensionAttributes = $this->cartExtensionFactory->create();
            $quote->setExtensionAttributes($extensionAttributes);
        }
        if (!$force && !empty($extensionAttributes->getAmastyOrderAttributes())) {
            return;
        }

        $entity = $this->entityResolver->getEntityByQuoteId($quote->getId());
        $customAttributes = $entity->getCustomAttributes();

        if (!empty($customAttributes)) {
            $extensionAttributes->setAmastyOrderAttributes($customAttributes);
        }
        $quote->setExtensionAttributes($extensionAttributes);
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     */
    public function saveQuoteValues(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $extensionAttributes = $quote->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getAmastyOrderAttributes()) {
            $entity = $this->entityResolver->getEntityByQuoteId($quote->getId());
            $attributes = $extensionAttributes->getAmastyOrderAttributes();

            foreach ((array)$attributes as $key => $attribute) {
                if ($this->forbidValidator->shouldDeleteAttributeValue($quote, $attribute->getAttributeCode())) {
                    $forbidAttributeCodes = $entity->getForbiddenAttributeCodes() ?? [];
                    $forbidAttributeCodes[] = $attribute->getAttributeCode();
                    $entity->setForbiddenAttributeCodes($forbidAttributeCodes);
                    unset($attributes[$key]);
                }
            }

            $entity->setCustomAttributes($attributes);
            $this->saveHandler->execute($entity);
        }
    }
}
