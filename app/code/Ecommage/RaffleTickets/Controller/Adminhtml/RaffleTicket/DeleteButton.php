<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Controller\Adminhtml\RaffleTicket;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Ecommage\RaffleTickets\Model\RaffleTicketsFactory;

class DeleteButton extends \Magento\Backend\App\Action
{
   /**
    * @var \Magento\Framework\View\Result\PageFactory
    */
    private $resultPageFactory;

    /**
     * Constructor parameter
     *
     * @param Context                $context
     * @param PageFactory            $resultPageFactory
     * @param RaffleTicketsFactory   $raffleTicketsFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RaffleTicketsFactory $raffleTicketsFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->raffleTicketsFactory = $raffleTicketsFactory;
        parent::__construct($context);
    }
    /**
     * Execute
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->raffleTicketsFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('Deleted Successfully!'));
                return $resultRedirect->setPath('*/*/');
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/addwinner', ['id' => $id]);
            }

            $this->messageManager->addError(__('We can\'t find data to delete.'));
            return $resultRedirect->setPath('*/*/');
        }
    }
}
