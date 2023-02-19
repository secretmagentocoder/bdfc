<?php

namespace Bdfc\General\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Setup\Exception;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\StoreManagerInterface;

class Index extends Action
{
    public function __construct
    (
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        Context $context,
        Session $checkoutSession,
        ResultFactory $resultFactory
    )
    {
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->resultFactory = $resultFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $this->checkoutSession->setAnotherPersonInfo($this->getRequest()->getParams());
            $dataResponse = true;
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            return $resultJson->setData($dataResponse);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
