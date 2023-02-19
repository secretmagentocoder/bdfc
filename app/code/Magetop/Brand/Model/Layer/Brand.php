<?php
/**
 * Magetop
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Magetop
 * @package    Magetop_Brand
 * @copyright  Copyright (c) 2014 Magetop (https://www.magetop.com/)
 * @license    https://www.magetop.com/LICENSE.txt
 */
namespace Magetop\Brand\Model\Layer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Resource;

class Brand extends \Magento\Catalog\Model\Layer
{
    /**
     * Retrieve current layer product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection()
    {
    	$brand = $this->getCurrentBrand();
    	if(isset($this->_productCollections[$brand->getId()])){
    		$collection = $this->_productCollections;
    	}else{
    		$collection = $brand->getProductCollection();
    		$this->prepareProductCollection($collection);
            $this->_productCollections[$brand->getId()] = $collection;
    	} 
    	return $collection;
    }

    /**
     * Retrieve current category model
     * If no category found in registry, the root will be taken
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentBrand()
    {
    	$brand = $this->getData('current_brand');
    	if ($brand === null) {
    		$brand = $this->registry->registry('current_brand');
    		if ($brand) {
    			$this->setData('current_brand', $brand);
    		}
    	}
    	return $brand;
    }
}