<?php

namespace Ecommage\RaffleTickets\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;

class Reload extends \Magento\Backend\App\Action 
{
    public function __construct
    (
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Context $context
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ecommage_RaffleTickets::synchronized');
        $resultPage->getConfig()->getTitle()->prepend(__('Upload Data'));
        return $resultPage;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecommage_RaffleTickets::synchronized');
    }
}