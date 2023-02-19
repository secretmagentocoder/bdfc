<?php

namespace Amasty\Orderattr\Controller\Adminhtml\Relation;

use Amasty\Orderattr\Controller\RegistryConstants;
use Magento\Backend\App\Action;
use Amasty\Orderattr\Api\RelationRepositoryInterface;
use Amasty\Orderattr\Model\Attribute\Relation\RelationFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;

class Edit extends \Amasty\Orderattr\Controller\Adminhtml\Relation
{
    /**
     * @var RelationRepositoryInterface
     */
    private $repository;

    /**
     * @var RelationFactory
     */
    private $relationFactory;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(
        Action\Context $context,
        RelationRepositoryInterface $repository,
        RelationFactory $relationFactory,
        Registry $coreRegistry,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->relationFactory = $relationFactory;
        $this->coreRegistry = $coreRegistry;
        $this->dataPersistor = $dataPersistor;
    }

    public function execute()
    {
        $relationId = $this->getRequest()->getParam('relation_id');
        if ($relationId) {
            try {
                $model = $this->repository->get($relationId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This Relation does not exist.'));
                $this->_redirect('*/*/');

                return;
            }
        } else {
            /** @var \Amasty\Orderattr\Model\Attribute\Relation\Relation $model */
            $model = $this->relationFactory->create();
        }

        if ($savedData = $savedData = $this->dataPersistor->get('amasty_order_attribute_relation')) {
            $model->addData($savedData);
            $this->dataPersistor->clear('amasty_order_attribute_relation');
        }

        $this->coreRegistry->register(RegistryConstants::CURRENT_RELATION_ID, $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $title = $model->getName() ? __("Edit Relation \"%1\"", $model->getName()) : __('New Order Attribute Relation');

        $resultPage->setActiveMenu('Amasty_Orderattr::attributes_relation');
        $resultPage->addBreadcrumb(__('Attribute Relation'), __('Attribute Relation'));
        $resultPage->addBreadcrumb($title, $title);
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
