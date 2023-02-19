<?php
namespace Ecommage\CustomerCategory\Block;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Posts extends Template implements BlockInterface {

    protected $_template = "widget/posts.phtml";

    protected $_storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Template\Context $context,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getStoreManagerData()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) ;
    }

    public function getUrlImage($urlImage){
        if (preg_match('/(___directive\/)([a-zA-Z0-9,_-]+)/', $urlImage, $matches)) {
            $directive = base64_decode(strtr($matches[2], '-_,', '+/='));
            $params = str_replace(['{{media url="', '"}}'], ['', ''], $directive);
            return $params;
        }
    }

}
