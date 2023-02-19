<?php

namespace Ecommage\CustomerOrderHistory\Plugin\Controller\Order;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class History
{
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $_redirect,
        PageFactory $resultPageFactory
    ) {
        $this->_redirect = $_redirect;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Customer order history
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function aroundExecute(\Magento\Sales\Controller\Order\History $subject ,callable $proceed)
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__(' '));

        $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        return $resultPage;
    }
}