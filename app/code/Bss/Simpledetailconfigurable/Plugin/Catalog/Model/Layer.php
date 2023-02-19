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
namespace Bss\Simpledetailconfigurable\Plugin\Catalog\Model;

class Layer
{
    const ENGINES = ['elasticsearch5', 'elasticsearch6', 'elasticsearch7'];

    /**
     * @var \Magento\Search\Model\EngineResolver
     */
    protected $engineResolver;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $helper;

    /**
     * Layer constructor.
     *
     * @param \Magento\Search\Model\EngineResolver $engineResolver
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
     */
    public function __construct(
        \Magento\Search\Model\EngineResolver $engineResolver,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
    ) {
        $this->engineResolver = $engineResolver;
        $this->helper = $helper;
    }

    /**
     * Filter parent product enable config redirect in search page & catalog page if disable elasticsearch.
     *
     * @param \Magento\Catalog\Model\Layer $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $result
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetProductCollection(\Magento\Catalog\Model\Layer $subject, $result)
    {
        if (!$this->helper->isModuleEnable()) {
            return $result;
        }

        try {
            $engine = $this->engineResolver->getCurrentSearchEngine();
            if (in_array($engine, self::ENGINES)) {
                return $result;
            }

            $result->addAttributeToFilter(
                [
                    ['attribute' => 'only_display_product_page', 'null' => true],
                    ['attribute' => 'only_display_product_page', 'eq' => '0']
                ]
            );

            return $result;
        } catch (\Exception $e) {
            return $result;
        }
    }
}
