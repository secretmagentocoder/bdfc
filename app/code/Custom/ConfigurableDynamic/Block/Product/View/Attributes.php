<?php

namespace Custom\ConfigurableDynamic\Block\Product\View;

use Magento\Catalog\Model\Product;

class Attributes extends \Magento\Catalog\Block\Product\View\Attributes
{
    public function setProduct(Product $product)
    {
        $this->_product = $product;
    }

    public function getCacheKeyInfo()
    {
        $info = parent::getCacheKeyInfo();
        $info['product_id'] = $this->getProduct()->getId();
        return $info;
    }


}