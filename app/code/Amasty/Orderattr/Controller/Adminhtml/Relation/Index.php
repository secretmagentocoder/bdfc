<?php

namespace Amasty\Orderattr\Controller\Adminhtml\Relation;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Amasty\Orderattr\Controller\Adminhtml\Relation
{
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('Amasty_Orderattr::attributes_relation');
        $resultPage->addBreadcrumb(__('Order Attribute'), __('Order Attribute'));
        $resultPage->addBreadcrumb(__('Attribute Relation'), __('Attribute Relation'));
        $resultPage->getConfig()->getTitle()->prepend(__('Order Attribute Relations'));

        return $resultPage;
    }
}
