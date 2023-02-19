<?php
/**
 * 
 * @package Bdfc_General
 */
declare(strict_types=1);

namespace Bdfc\General\Helper;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class ProductConfig extends \Magento\Search\Helper\Data
{
    private $categoryCollection;
 
    public function __construct(
    \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
    ) {
        $this->categoryCollection = $categoryCollection;
    }
 
    public function getAgeLimit($categories)
    {
        $ageData = ['age_limit' =>0, 'age_limit_category'=>''];
        
        $catCollection = $this->categoryCollection->create()->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', ['in' => $categories])
            ->addAttributeToFilter('age_limit', ['gt' => 0]);

        foreach ($catCollection as $cat) {
            $ageData['age_limit'] = $cat->getData('age_limit');
            $ageData['age_limit_category'] = $cat->getData('name');
        }
        return $ageData;
    }
}