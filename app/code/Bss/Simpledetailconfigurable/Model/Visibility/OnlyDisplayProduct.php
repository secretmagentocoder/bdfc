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
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Model\Visibility;

class OnlyDisplayProduct
{
    const ATTRIBUTE_CODE = "only_display_product_page";

    /**
     * @var array|null
     */
    protected $ids;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollection;

    /**
     * OnlyDisplayProduct constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
    ) {
        $this->productCollection = $productCollection;
    }

    /**
     * Get entity_ids: product config display only product
     *
     * @return array
     */
    public function getIDs()
    {
        if (!$this->ids) {
            $productCollection = $this->productCollection->create()->addAttributeToFilter(
                [
                    ['attribute' => self::ATTRIBUTE_CODE, 'eq' => '1']
                ]
            );
            $this->ids = $productCollection->getAllIds();
        }

        return $this->ids;
    }
}
