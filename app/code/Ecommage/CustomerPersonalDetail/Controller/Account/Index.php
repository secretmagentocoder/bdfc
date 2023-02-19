<?php

namespace Ecommage\CustomerPersonalDetail\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    protected $customerSession;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context     $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        if(!$this->customerSession->isLoggedIn()){
            return $this->_redirect('customer/account/login');
        }
        return $this->_resultPageFactory->create();
    }
}
