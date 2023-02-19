<?php

namespace Amasty\Orderattr\Controller\Adminhtml\Relation;

use Magento\Backend\App\Action;
use Amasty\Orderattr\Api\RelationRepositoryInterface;

class Delete extends \Amasty\Orderattr\Controller\Adminhtml\Relation
{
    /**
     * @var RelationRepositoryInterface
     */
    private $repository;

    public function __construct(
        Action\Context $context,
        RelationRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->repository = $repository;
    }

    public function execute()
    {
        if ($relationId = $this->getRequest()->getParam('relation_id')) {
            try {
                $this->repository->deleteById($relationId);
                $this->messageManager->addSuccessMessage(__('The Relation has been deleted.'));
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This Relation does not exist.'));
            }
        }

        $this->_redirect('*/*/');
    }
}
