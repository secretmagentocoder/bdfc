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
namespace Magetop\Brand\Controller;

use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Url;

class Router implements RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Response
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var bool
     */
    protected $dispatched;

    /**
     * Brand Factory
     *
     * @var \Magetop\Brand\Model\Brand $brandCollection
     */
    protected $_brandCollection;

    /**
     * Brand Factory
     *
     * @var \Magetop\Brand\Model\Group $groupCollection
     */
    protected $_groupCollection;

    /**
     * Brand Helper
     */
    protected $_brandHelper;

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ActionFactory          $actionFactory   
     * @param ResponseInterface      $response        
     * @param ManagerInterface       $eventManager    
     * @param \Magetop\Brand\Model\Brand $brandCollection 
     * @param \Magetop\Brand\Model\Group $groupCollection 
     * @param \Magetop\Brand\Helper\Data $brandHelper     
     * @param StoreManagerInterface  $storeManager    
     */
    public function __construct(
    	ActionFactory $actionFactory,
    	ResponseInterface $response,
        ManagerInterface $eventManager,
        \Magetop\Brand\Model\Brand $brandCollection,
        \Magetop\Brand\Model\Group $groupCollection,
        \Magetop\Brand\Helper\Data $brandHelper,
        StoreManagerInterface $storeManager
        )
    {
    	$this->actionFactory = $actionFactory;
        $this->eventManager = $eventManager;
        $this->response = $response;
        $this->_brandHelper = $brandHelper;
        $this->_brandCollection = $brandCollection;
        $this->_groupCollection = $groupCollection;
        $this->storeManager = $storeManager;
    }
    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface
     */
    public function match(RequestInterface $request)
    {
        $_brandHelper = $this->_brandHelper;
        if (!$this->dispatched) {
            $urlKey = trim($request->getPathInfo(), '/');
            $origUrlKey = $urlKey;
            /** @var Object $condition */
            $condition = new DataObject(['url_key' => $urlKey, 'continue' => true]);
            $this->eventManager->dispatch(
                'magetop_brand_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
                );
            $urlKey = $condition->getUrlKey();
            if ($condition->getRedirectUrl()) {
                $this->response->setRedirect($condition->getRedirectUrl());
                $request->setDispatched(true);
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Redirect',
                    ['request' => $request]
                    );
            }
            if (!$condition->getContinue()) {
                return null;
            }
            $route = $_brandHelper->getConfig('general_settings/route');
            $urlKeyArr = explode(".",$urlKey);
            if(count($urlKeyArr) > 1) {
                $urlKey = $urlKeyArr[0];
            }
            $routeArr = explode(".",$route);
            if(count($routeArr) > 1) {
                $route = $routeArr[0];
            }
            if( $route !='' && $urlKey == $route )
            {
                $request->setModuleName('magetopbrand')
                ->setControllerName('index')
                ->setActionName('index');
                $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
                $this->dispatched = true;
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Forward',
                    ['request' => $request]
                    );
            }
            $url_prefix = $_brandHelper->getConfig('general_settings/url_prefix');
            $url_suffix = $_brandHelper->getConfig('general_settings/url_suffix');
            $url_prefix = $url_prefix?$url_prefix:$route;
            $identifiers = explode('/',$urlKey);

            // SEARCH PAGE
            if(count($identifiers)==2 && $url_prefix == $identifiers[0] && $identifiers[1]=='search'){
                $request->setModuleName('magetopbrand')
                ->setControllerName('search')
                ->setActionName('result');
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                $request->setDispatched(true);
                $this->dispatched = true;
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Forward',
                    ['request' => $request]
                    );
            }
            //Check Group Url
            if( (count($identifiers) == 2 && $identifiers[0] == $url_prefix) || (trim($url_prefix) == '' && count($identifiers) == 1)){
                $brandUrl = '';
                if(trim($url_prefix) == '' && count($identifiers) == 1){
                    $brandUrl = str_replace($url_suffix, '', $identifiers[0]);
                }
                if(count($identifiers) == 2){
                    $brandUrl = str_replace($url_suffix, '', $identifiers[1]);
                }
                if ($brandUrl) {
                    $group = $this->_groupCollection->getCollection()
                    ->addFieldToFilter('status', array('eq' => 1))
                    ->addFieldToFilter('url_key', array('eq' => $brandUrl))
                    ->getFirstItem();

                    if($group && $group->getId()){
                        $request->setModuleName('magetopbrand')
                        ->setControllerName('group')
                        ->setActionName('view')
                        ->setParam('group_id', $group->getId());
                        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                        $request->setDispatched(true);
                        $this->dispatched = true;
                        return $this->actionFactory->create(
                            'Magento\Framework\App\Action\Forward',
                            ['request' => $request]
                            );
                    } else {
                        $brand = $this->_brandCollection->getCollection()
                                ->addFieldToFilter('status', array('eq' => 1))
                                ->addFieldToFilter('url_key', array('eq' => $brandUrl))
                                ->addStoreFilter([0,$this->storeManager->getStore()->getId()])
                                ->getFirstItem();

                        if($brand && $brand->getId()){
                            $request->setModuleName('magetopbrand')
                            ->setControllerName('brand')
                            ->setActionName('view')
                            ->setParam('brand_id', $brand->getId());
                            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                            $request->setDispatched(true);
                            $this->dispatched = true;
                            return $this->actionFactory->create(
                                'Magento\Framework\App\Action\Forward',
                                ['request' => $request]
                                );
                        }

                    }
                }
            }
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
            $request->setDispatched(true);
            $this->dispatched = true;
            return $this->actionFactory->create(
                'Magento\Framework\App\Action\Forward',
                ['request' => $request]
                );
        }
    }
}