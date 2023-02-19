<?php

namespace Ecommage\CustomerOrderHistory\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    protected $_productRepositoryFactory;
    public function __construct
    (
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Sales\Model\Order $order,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        Context $context
    )
    {
        $this->timezone = $timezone;
        $this->order = $order;
        $this->storeManager = $storeManager;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        parent::__construct($context);
    }

    public function getProduct($id)
    {
        $product = [];
        if ($id){
            $product =$this->_productRepositoryFactory->create()->getById($id);
        }
        return $product;
    }

    public function getStoreName($storeId)
    {
        return $this->storeManager->getStore($storeId)->getName();
    }

    public function formatDay($day, $type = 0)
    {
        $this->getConfigValue();
        $data = $this->timezone->date(new \DateTime($day))->format('H:i');
        if (empty($type)) {
            $data =  $this->timezone->date(new \DateTime($day))->format('j F, Y');
        }
        return  $data;
    }

    public function getConfigValue($storeId = null): array
    {
        $config = $this->scopeConfig->getValue('ecommage_display_video/general/link', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $param = '';
        if ($config && str_contains($config, 'v=')) {
            $url = explode('v=', $config);
            $param = explode('&', $url[1]);
            $param['type'] = 0;
        } else {
            $param = explode('/', $config);
            $param['type'] = 1;
        }

        return $param;
    }

    public function getUrlCollectionPoint()
    {
        $url = $this->scopeConfig->getValue('ecommage_display_video/general/link_point', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (empty($url))
        {
            $url =  $this->storeManager->getStore()->getBaseUrl();
        }
        return $url;
    }

    public function getValueSize($product)
    {
        $value = [];
        if ($product) {
            $value = $product->getResource()->getAttribute('size')->getFrontend()->getValue($product);
        }
        return $value;
    }

}