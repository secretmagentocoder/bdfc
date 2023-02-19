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
namespace Magetop\Brand\Block;
use Magento\Customer\Model\Context as CustomerContext;

class BrandList extends \Magento\Framework\View\Element\Template
{
    /**
     * Group Collection
     */
    protected $_brandCollection;

    protected $_collection = null;

	/**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_brandHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context         
     * @param \Magento\Framework\Registry                      $registry        
     * @param \Magetop\Brand\Helper\Data                           $brandHelper     
     * @param \Magetop\Brand\Model\Brand                           $brandCollection 
     * @param array                                            $data            
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magetop\Brand\Helper\Data $brandHelper,
        \Magetop\Brand\Model\Brand $brandCollection,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
        ) {
        $this->_brandCollection = $brandCollection;
        $this->_brandHelper = $brandHelper;
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }

    public function _construct(){
        if(!$this->getConfig('general_settings/enable') || !$this->getConfig('brand_block/enable')) return;
        parent::_construct();
        $carousel_layout = $this->getConfig('brand_block/carousel_layout');
        $template = '';
        if($carousel_layout == 'owl_carousel'){
            $template = 'block/brand_list_owl.phtml';
        }else{
            $template = 'block/brand_list_bootstrap.phtml';
        }
        if(!$this->getTemplate() && $template!=''){
            $this->setTemplate($template);
        }
    }

    public function getConfig($key, $default = '')
    {   
        $widget_key = explode('/', $key);
        if( (count($widget_key)==2) && ($resultData = $this->hasData($widget_key[1])) )
        {
            return $this->getData($widget_key[1]);
        }
        $result = $this->_brandHelper->getConfig($key);
        if($result == ""){
            return $default;
        }
        return $result;
    }

    public function getBrandCollection()
    {
        if(!$this->_collection) {
            $number_item = $this->getConfig('brand_block/number_item');
            $brandGroups = $this->getConfig('brand_block/brand_groups');
            $store = $this->_storeManager->getStore();
            $collection = $this->_brandCollection->getCollection()
            ->setOrder('name','ASC')
            ->addStoreFilter($store)
            ->addFieldToFilter('status',1);
            $brandGroups = explode(',', $brandGroups);
            if(is_array($brandGroups) && count($brandGroups)>0)
            {
                $collection->addFieldToFilter('group_id',array('in' => $brandGroups));
            }
            $collection->setPageSize($number_item)
            ->setCurPage(1)
            ->setOrder('name','ASC');
            $this->_collection = $collection;
        }
        return $this->_collection;
    }


    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return [
        'MAGETOP_BRAND_LIST',
        $this->_storeManager->getStore()->getId(),
        $this->_design->getDesignTheme()->getId(),
        $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP),
        'template' => $this->getTemplate(),
        $this->getProductsCount()
        ];
    }

    public function _toHtml()
    {
        return parent::_toHtml();
    }
}