<?php

namespace Ecommage\CustomerOrderHistory\Controller\Index;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;

class Search extends Action
{
    public function __construct(
        Registry $registry,
        Page $page,
        Context $context
    ) {
        $this->_registry= $registry;
        $this->page = $page;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $text = $this->getRequest()->getParam('text', null);
        $block = $this->page->getLayout()->createBlock('Ecommage\CustomerOrderHistory\Block\Order\History')->setData('display',1)->toHtml();
        if ($text) {
            $this->_registry->register('text_search', $text);
            $block =  $this->page->getLayout()->createBlock('Ecommage\CustomerOrderHistory\Block\Order\History', 'ecommage_product_order_seach', ['params'=> $text,'display' => 1 ])->setTemplate('Ecommage_CustomerOrderHistory::order/search/history-search.phtml')->toHtml();
        }
        return $resultJson->setData(
            [
                'html'     => $block,
            ]
        );
    }
}
