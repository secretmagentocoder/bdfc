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
namespace Bss\MinMaxQtyOrderPerCate\Block\Adminhtml\System\Config\Form;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class Category extends Select
{
    /**
     * Category Collection
     *
     * @var CollectionFactory
     */
    protected $categoryCollection;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Construct
     *
     * @param Context $context
     * @param CollectionFactory $categoryCollection
     */
    public function __construct(
        Context $context,
        CollectionFactory $categoryCollection,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->categoryCollection = $categoryCollection;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * Get Category Helper
     *
     * @return \Magento\Framework\View\Element\Html\Select
     */
    public function getCategoryHelper()
    {
        return $this->_categoryHelper;
    }

    /**
     * @param bool $bssAddEmpty
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function toOptionArray($bssAddEmpty = true)
    {
        $iStoreId = $this->request->getParam('store', '0');

        $oCategoryCollection = $this->categoryCollection->create()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active')
            ->addAttributeToSelect('parent_id')
            ->setStoreId($iStoreId)
            ->addFieldToFilter('parent_id', ['gt' => 0])
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('level', ['gteq' => 1])
            ->addAttributeToSort('path', 'asc');
        $aOptions = [];
        
        if ($bssAddEmpty) {
            $aOptions[] = [
                'label' => '-- Please Select a Category --',
                'value' => ''
            ];
        }
        foreach ($oCategoryCollection as $oCategory) {
            $categoryName = $this->escapeHtml($oCategory->getName());
            $sLabel = $categoryName."(ID: ".$oCategory->getId().")";
            $iPadWidth = ($oCategory->getLevel() - 1) * 2 + strlen($sLabel);
            $sLabel = str_pad($sLabel, $iPadWidth, '---', STR_PAD_LEFT);
 
            $aOptions[] = [
                'label' => $sLabel,
                'value' => $oCategory->getId()
            ];
        }

        return $aOptions;
    }

    /**
     * To Html
     *
     * @return string
     */
    public function _toHtml()
    {
        $options =  $this->toOptionArray();
        foreach ($options as $option) {
            $this->addOption($option['value'], $option['label']);
        }

        return parent::_toHtml();
    }

    /**
     * Set Input Name
     *
     * @param string $value
     * @return string
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
