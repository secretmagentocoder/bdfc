<?php

namespace Ecommage\OrderRaffle\Controller\Order;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\PageFactory;

class Raffle extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Session     $customerSession
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Session $customerSession,
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->session = $customerSession;
        $this->_resultPageFactory  = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->session->isLoggedIn()) {
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('customer/account/login');
        }

        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }
}