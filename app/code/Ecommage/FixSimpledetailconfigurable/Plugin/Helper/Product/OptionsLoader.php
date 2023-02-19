<?php

namespace Ecommage\FixSimpledetailconfigurable\Plugin\Helper\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Helper\Product\Options\Loader;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class OptionsLoader
 */
class OptionsLoader
{
    /**
     * @param Loader           $subject
     * @param callable         $proceed
     * @param ProductInterface $product
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundLoad(Loader $subject, callable $proceed, ProductInterface $product)
    {
        /** @var Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance();
        if (!in_array('getConfigurableAttributeCollection', get_class_methods($typeInstance))) {
            return [];
        }

        return $proceed($product);
    }
}
