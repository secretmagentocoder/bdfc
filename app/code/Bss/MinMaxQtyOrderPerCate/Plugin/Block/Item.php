<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MinMaxQtyOrderPerCate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MinMaxQtyOrderPerCate\Plugin\Block;

class Item
{
    /**
     * Helper
     *
     * @var \Bss\MinMaxQtyOrderPerCate\Helper\Data
     */
    protected $minmaxHelper;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Bss\MinMaxQtyOrderPerCate\Helper\Data $minmaxHelper
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Bss\MinMaxQtyOrderPerCate\Helper\Data $minmaxHelper
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->minmaxHelper = $minmaxHelper;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProduct($subject, $result)
    {
        if ($this->minmaxHelper->getConfig('enable')) {
            if ($this->minmaxHelper->getConfig('show_category')) {
                $categoryIds = $result->getCategoryIds();
                $categoryCollection = $this->categoryFactory->create()->getCollection();
                $categoryCollection->addAttributeToSelect('name');
                $categoryCollection->addFieldToFilter('entity_id', ['in' => $categoryIds]);
                $category_names = '';
                if ($categoryCollection->getSize() > 0) {
                    foreach ($categoryCollection as $category) {
                        $array_category_names[] = $category->getName();
                    }
                    $category_names = implode(',', $array_category_names);
                }
                $subject->setBssCategoryNames($category_names);
            }
        }
        return $result;
    }
}
