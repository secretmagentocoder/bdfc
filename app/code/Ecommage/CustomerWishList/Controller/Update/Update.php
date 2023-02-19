<?php

namespace Ecommage\CustomerWishList\Controller\Update;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;

class Update extends Action
{
    protected $logger;

    protected $resultJsonFactory;

    protected $wishList;

    public function __construct
    (
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        LoggerInterface   $logger,
        \Magento\Wishlist\Model\Item $item,
        Context $context
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->wishList = $item;
        parent::__construct($context);
    }

    public function execute()
    {
        $error = '';
        $resultJson = $this->resultJsonFactory->create();
        $id = $this->getRequest()->getParam('id');
        $qty = $this->getRequest()->getParam('qty');
        if ($id && $qty){
            try {
                $wishList = $this->wishList->load($id);
                if ($wishList){
                    $wishList->setQty((int) $qty );
                    $wishList->save();
                    $messages = __('Update qty wish list successfully');
                }
            }catch (\Exception $exception){
                $messages = __($exception->getMessage());
                $error = $exception->getCode();
                $this->logger->error($exception->getMessage());
            }
        }
        return $resultJson->setData(
            [
                'messages' => $messages,
                'error' => $error
            ]
        );
    }
}