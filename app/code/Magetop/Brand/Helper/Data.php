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
namespace Magetop\Brand\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Group Collection
     */
    protected $_groupCollection;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * Brand config node per website
     *
     * @var array
     */
    protected $_config = [];

    /**
     * Template filter factory
     *
     * @var \Magento\Catalog\Model\Template\Filter\Factory
     */
    protected $_templateFilterFactory;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    protected $_request;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magetop\Brand\Model\Group $groupCollection,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider
        ) {
        parent::__construct($context);
        $this->_filterProvider = $filterProvider;
        $this->_storeManager = $storeManager;
        $this->_groupCollection = $groupCollection;
        $this->_request            = $context->getRequest();
    }

    public function getGroupList(){
        $result = array();
        $collection = $this->_groupCollection->getCollection()
        ->addFieldToFilter('status', '1');
        foreach ($collection as $brandGroup) {
            $result[$brandGroup->getId()] = $brandGroup->getName();
        }
        return $result;
    }

    /**
     * Return brand config value by key and store
     *
     * @param string $key
     * @param \Magento\Store\Model\Store|int|string $store
     * @return string|null
     */
    public function getConfig($key, $store = null)
    {
        $store = $this->_storeManager->getStore($store);
        $websiteId = $store->getWebsiteId();

        $result = $this->scopeConfig->getValue(
            'magetopbrand/'.$key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);
        return $result;
    }

    public function filter($str)
    {
        $html = $this->_filterProvider->getPageFilter()->filter($str);
        return $html;
    }

    public function getSearchFormUrl(){
        $url        = $this->_storeManager->getStore()->getBaseUrl();
        $url_prefix = $this->getConfig('general_settings/url_prefix');
        $url_suffix = $this->getConfig('general_settings/url_suffix');
        $urlPrefix  = '';
        if ($url_prefix) {
            $urlPrefix = $url_prefix . '/';
        }
        return $url . $urlPrefix . 'search';
    }
    public function getSearchKey(){
        return $this->_request->getParam('s');
    }

}