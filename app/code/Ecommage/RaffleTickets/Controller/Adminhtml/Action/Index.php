<?php

namespace Ecommage\RaffleTickets\Controller\Adminhtml\Action;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;


class Index extends \Magento\Backend\App\Action
{

    const PATH = 'ecommage';

    public function __construct
    (
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        ResourceConnection $resourceConnection,
        \Ecommage\RaffleTickets\Helper\Data $helperData,
        \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $collectionFactory,
        \Ecommage\CreateProductApi\Helper\Data $helperApi,
        ManagerInterface $messageManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        LoggerInterface $logger,
        \Magento\Framework\App\Config\Value $valueConfig,
        \Magento\Config\Model\ResourceModel\Config\Data $resourceData ,
        Context $context
    )
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->resource = $resourceConnection;
        $this->helper = $helperData;
        $this->helperApi = $helperApi;
        $this->collectionFactory = $collectionFactory;
        $this->messageManager = $messageManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->_logger = $logger;
        $this->valueConfig = $valueConfig;
        $this->resourceData = $resourceData;
        parent::__construct($context);
    }

//    public function execute()
//    {
//        $type = $this->getRequest()->getParam('type',null);
//        $resultRedirect = $this->resultRedirectFactory->create();
//        $config = $this->helper->getConfigIsCheck($type);
////        dd($config);
//        if (empty($config))
//        {
//            try {
//                $path = !empty($type) ? sprintf("%s/%s",self::PATH,$type) : sprintf("%s/%s",self::PATH,'create_virtual_product') ;
//                $this->valueConfig->setPath($path);
//                $this->valueConfig->setValue(1);
//                $this->valueConfig->setScopeId(0);
//                $this->valueConfig->setScope('default');
//                $this->valueConfig->save();
//                $this->messageManager->addSuccessMessage(__('We updated queue request.'));
//
//            }catch (\Exception $e)
//            {
//                $this->messageManager->addErrorMessage(__($e->getMessage()));
//                $this->_logger->error($e->getMessage());
//            }
//            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
//            return $resultRedirect;
//        }
//
//        $this->messageManager->addSuccessMessage(__('We updated queue request.'));
//        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
//        return $resultRedirect;
//    }

        public function execute()
        {
            $type = $this->getRequest()->getParam('type',null);
            $resultRedirect = $this->resultRedirectFactory->create();
                try {
                    if (empty($type)){
                       $this->helper->setProduct($this->helper->getConfigLinkApi());
                        foreach ($this->getProductCollection() as $item)
                        {
                            $optionId = current($this->helper->getProductOptionId($item->getId(),0));
                            if ($optionId){
                                $this->unsetColumn($optionId->getOptionId());
                                $this->updateDelete($optionId->getOptionId());
                            }
                        }
                    }
                    if ($type == 'variant')
                    {
                       $this->helperApi->setSdcp();
                    }
                    if ($type == 'manual')
                    {
                        $this->helperApi->setVariant();
                    }
                    if ($type == 'color')
                    {
                        $this->helperApi->setHexCodeProduct();
                    }
                    $this->messageManager->addSuccessMessage(__('The data has been updated.'));
                }catch (\Exception $e)
                {
                    $this->messageManager->addErrorMessage(__($e->getMessage()));
                    $this->_logger->error($e->getMessage());
                }

            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

    public function updateDelete($optionId)
    {
        $connection = $this->resource->getConnection();
        try {
            $connection->update(
                'catalog_product_option_type_value',
                [
                    'delete' => 0,
                ],
                [
                    'option_id' => $optionId,
                ]
            );
        }catch (\Exception $e){
            $this->_logger->error($e->getMessage());
        }

    }

    public function unsetColumn($optionId)
    {
        $connection = $this->resource->getConnection();
        try {
            $connection->delete('catalog_product_option_type_value',
                                [
                                    'option_id = ?' => $optionId,
                                    'disable = ?'   => 0,
                                ]
            );
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }

    public function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToFilter('is_check_raffle',1);
        return $collection;
    }
}
