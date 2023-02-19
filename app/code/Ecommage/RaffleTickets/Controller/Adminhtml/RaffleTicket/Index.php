<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Controller\Adminhtml\RaffleTicket;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context        $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /**
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('Raffle Ticket Winners'));
        return $resultPage;
    }

    /**
     * Init page
     *
     * @param  \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Ecommage_RaffleTickets::raffleticket')
            ->addBreadcrumb(__('raffleticket'), __('raffleticket'))
            ->addBreadcrumb(__('raffleticket'), __(''));
        return $resultPage;
    }
    /**
     * IsAllowed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecommage_RaffleTickets::raffleticket');
    }
}
