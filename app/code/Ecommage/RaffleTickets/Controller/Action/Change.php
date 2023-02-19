<?php

namespace Ecommage\RaffleTickets\Controller\Action;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;

class Change extends Action
{
    public function __construct
    (
        \Magento\Catalog\Model\ProductRepository $productRepository,
        Registry $registry,
        Page $page,
        Context $context
    )
    {
        $this->_productRepository = $productRepository;
        $this->_registry= $registry;
        $this->page = $page;
        parent::__construct($context);
    }

    public function execute()
    {
        $type = $this->getRequest()->getParam('type',null);
        $productId = $this->getRequest()->getParam('id',null);
        $product = $this->_productRepository->getById($productId);
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if ($type) {
            $this->_registry->register('type_number', $type);
            $this->_registry->register('current_product', $product);
            $block =  $this->page->getLayout()->createBlock('Ecommage\RaffleTickets\Block\Raffle\ChangeNumber', 'ecommage_category_tickets')->setData('type_number',$type)->toHtml();
            $this->_registry->unregister('type_number');
            $this->_registry->unregister('current_product');
        }
        return $resultJson->setData(
            [
                'html'     => $block,
            ]
        );
    }
}
