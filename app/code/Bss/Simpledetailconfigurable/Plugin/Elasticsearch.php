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
namespace Bss\Simpledetailconfigurable\Plugin;

class Elasticsearch
{
    /**
     * @var \Bss\Simpledetailconfigurable\Model\Visibility\OnlyDisplayProduct
     */
    protected $onlyDisplayProduct;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $helper;

    /**
     * Elasticsearch constructor.
     *
     * @param \Bss\Simpledetailconfigurable\Model\Visibility\OnlyDisplayProduct $onlyDisplayProduct
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Model\Visibility\OnlyDisplayProduct $onlyDisplayProduct,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
    ) {
        $this->onlyDisplayProduct = $onlyDisplayProduct;
        $this->helper = $helper;
    }

    /**
     * Must not ids: product config display only product page
     *
     * @param mixed $subject
     * @param array $query
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeQuery($subject, $query)
    {
        if (!$this->helper->isModuleEnable()) {
            return null;
        }

        $ids = $this->onlyDisplayProduct->getIDs();
        if (count($ids)) {
            $ids = $this->mergeIds($query, $ids);
            $query['body']['query']['bool']['must_not'] = ['ids' => ['values' => $ids]];
        }

        return [$query];
    }

    /**
     * Merge ids
     *
     * @param array $query
     * @param array $ids
     * @return array
     */
    public function mergeIds($query, $ids)
    {
        try {
            if (isset($query['body']['query']['bool']["must_not"])) {
                $idsOld = $query['body']['query']['bool']["must_not"];
                $ids = array_merge($ids, $idsOld);
                return array_unique($ids);
            }
        } catch (\Exception $e) {
            return $ids;
        }
        return $ids;
    }
}
