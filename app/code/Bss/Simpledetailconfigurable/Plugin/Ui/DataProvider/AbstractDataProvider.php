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
namespace Bss\Simpledetailconfigurable\Plugin\Ui\DataProvider;

use Bss\Simpledetailconfigurable\Override\Catalog\Model\Product\Visibility;

class AbstractDataProvider
{
    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $helper;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * Constructor.
     *
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->helper = $helper;
        $this->eavAttribute = $eavAttribute;
        $this->resource = $resource;
    }

    /**
     * Change filter visibility.
     *
     * @param \Magento\Ui\DataProvider\AbstractDataProvider $subject
     * @param \Magento\Framework\Api\Filter $filter
     * @return \Magento\Framework\Api\Filter|null|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeAddFilter(\Magento\Ui\DataProvider\AbstractDataProvider $subject, $filter)
    {
        if (!$this->helper->isModuleEnable()) {
            return null;
        }

        if ($filter->getField() == 'visibility') {
            if ($filter->getValue() == Visibility::VISIBILITY_REDIRECT) {
                $filter->setField('only_display_product_page');
                $filter->setValue('1');
            } else {
                $attributeId = $this->eavAttribute
                    ->getIdByCode(\Magento\Catalog\Model\Product::ENTITY, 'only_display_product_page');

                $tableName = $this->resource->getTableName('catalog_product_entity_int');
                $subject->getCollection()->getSelect()->joinLeft(
                    ['sdcp_only_display_product_page' => $tableName],
                    sprintf(
                        "(`sdcp_only_display_product_page`.`entity_id` = %s)
                        AND (`sdcp_only_display_product_page`.`attribute_id` = %s)
                        AND (`sdcp_only_display_product_page`.`store_id` = %s)",
                        "`e`.`entity_id`",
                        $attributeId,
                        0
                    ),
                    ['sdcp_only_display_product_page_value' => 'value']
                );
                $subject->getCollection()->getSelect()->where(
                    sprintf(
                        "sdcp_only_display_product_page.value %s OR sdcp_only_display_product_page.value = %s",
                        "IS NULL",
                        0
                    )
                );
            }
        }

        return [$filter];
    }
}
