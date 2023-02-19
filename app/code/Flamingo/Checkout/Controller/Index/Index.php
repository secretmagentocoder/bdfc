<?php

namespace Flamingo\Checkout\Controller\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
	
    public function execute()
    {
		$this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
		//$resultRedirect = $this->resultRedirectFactory->create();
		//return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
		//return $this->resultPageFactory->create();
    }
}