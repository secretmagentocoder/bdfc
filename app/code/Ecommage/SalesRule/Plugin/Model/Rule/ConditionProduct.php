<?php

namespace Ecommage\SalesRule\Plugin\Model\Rule;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\Model\AbstractModel;
use Magento\SalesRule\Model\Rule\Condition\Product;
use Psr\Log\LoggerInterface;

/**
 * Class ConditionProduct
 */
class ConditionProduct extends AbstractRule
{
    /**
     * @param Product       $subject
     * @param AbstractModel $model
     */
    public function beforeValidate(Product $subject, AbstractModel $model)
    {
        $attrCode = $subject->getAttribute();
        if ($attrCode === 'sku' && $subject->getOperator() === '()') {
            $skus = $subject->getValue();
            $this->registry->unregister(self::SKU_IS_ONE_OF);
            $this->registry->register(self::SKU_IS_ONE_OF, $skus);
            $objectManager = ObjectManager::getInstance();
            $logger        = $objectManager->create(LoggerInterface::class);
            $logger->debug('----------------------TEST-------------------------' . $skus);
            $logger->debug(__FILE__);
        }
    }
}
