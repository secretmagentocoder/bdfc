<?php

namespace Amasty\Orderattr\Controller\Adminhtml\Order;

use Amasty\Orderattr\Api\Data\CheckoutEntityInterface;
use Amasty\Orderattr\Model\Entity\EntityResolver;
use Amasty\Orderattr\Model\Value\Metadata\FormFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Amasty\Orderattr\Model\Value\Metadata\Form;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * @deprecated
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_order';

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    /**
     * @var FormFactory
     */
    private $metadataFormFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Amasty\Orderattr\Model\Entity\Handler\Save
     */
    private $saveHandler;

    public function __construct(
        Context $context,
        EntityResolver $entityResolver,
        FormFactory $metadataFormFactory,
        OrderRepositoryInterface $orderRepository,
        \Amasty\Orderattr\Model\Entity\Handler\Save $saveHandler
    ) {
        parent::__construct($context);
        $this->entityResolver = $entityResolver;
        $this->metadataFormFactory = $metadataFormFactory;
        $this->orderRepository = $orderRepository;
        $this->saveHandler = $saveHandler;
    }

    public function execute()
    {
        return;
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $postItems   = $this->getRequest()->getParam('items', []);

        $errors = [];

        foreach ($postItems as $orderId => $postData) {
            $entity = $this->entityResolver->getEntityByOrder($this->orderRepository->get((int)$orderId));

            $form = $this->createEntityForm($entity, 'adminhtml_order_inline_edit');
            //$entity->setCustomAttributes([]);
            $request = $form->prepareRequest($postData);
            $data = $form->extractData($request);
            $form->restoreData($data);

            $validationErrors = $form->validateData($data);
            if (is_array($validationErrors)) {
                $errors = array_merge($errors, $validationErrors);
            } else {
                $this->saveHandler->execute($entity);
            }
        }

        return $resultJson->setData(
            [
                'messages' => $errors,
                'error'    => !empty($errors)
            ]
        );
    }

    /**
     * Return Checkout Form instance
     *
     * @param \Amasty\Orderattr\Model\Entity\EntityData $entity
     * @param string $checkoutFormCode
     *
     * @return Form
     */
    protected function createEntityForm($entity, $checkoutFormCode)
    {
        /** @var Form $formProcessor */
        $formProcessor = $this->metadataFormFactory->create();
        $formProcessor->setFormCode($checkoutFormCode)
            ->setEntity($entity);

        if ($entity->getParentId() && $entity->getParentEntityType() == CheckoutEntityInterface::ENTITY_TYPE_ORDER) {
            $formProcessor->setStore($this->orderRepository->get((int)$entity->getParentId())->getStore());
        }

        return $formProcessor;
    }
}
