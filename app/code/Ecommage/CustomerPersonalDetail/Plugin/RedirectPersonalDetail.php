<?php

namespace Ecommage\CustomerPersonalDetail\Plugin;

class RedirectPersonalDetail {

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     */
    public function __construct(
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
    )
    {
        $this->redirectFactory  =  $redirectFactory;
    }

    /**
     * @param \Magento\Customer\Controller\Account\Index $subject
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(\Magento\Customer\Controller\Account\Index $subject){
        $resultRedirect = $this->redirectFactory->create();
        $resultRedirect->setPath('ecommage_customer_update/account/index/');
        return $resultRedirect;
    }
}
