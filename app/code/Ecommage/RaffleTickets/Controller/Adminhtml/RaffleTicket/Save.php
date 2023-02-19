<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Controller\Adminhtml\RaffleTicket;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Ecommage\RaffleTickets\Api\Data\RaffleTicketsInterfaceFactory;
use Ecommage\RaffleTickets\Api\RaffleTicketsRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Save extends Action
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RaffleTicketsInterfaceFactory
     */
    private $raffleTicketsFactory;

    /**
     * @var RaffleTicketsRepositoryInterface
     */
    private $raffleTicketsRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    
    /**
     * Constructors.
     *
     * @param Context $context
     * @param RequestInterface $request
     * @param RaffleTicketsInterfaceFactory $raffleTicketsFactory
     * @param RaffleTicketsRepositoryInterface $raffleTicketsRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        RaffleTicketsInterfaceFactory $raffleTicketsFactory,
        RaffleTicketsRepositoryInterface $raffleTicketsRepository,
        DataObjectHelper $dataObjectHelper,
        ProductRepositoryInterface $productRepository
    ) {
        $this->request = $request;
        $this->raffleTicketsFactory = $raffleTicketsFactory;
        $this->raffleTicketsRepository = $raffleTicketsRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * Function execute
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if (isset($data['raffle_product_id'])) {
            $productId = $data['raffle_product_id'];
            $product = $this->productRepository->getById($productId);
            $data['raffle_product_series'] = $product->getSeries();
            $data['raffle_product_name'] = $product->getName();
        }
        $saveWinner = $this->raffleTicketsFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $saveWinner,
            $data,
            \Ecommage\RaffleTickets\Api\Data\RaffleTicketsInterface::class
        );
        $this->raffleTicketsRepository->save($saveWinner);
        $this->messageManager->addSuccess(__('Winner Information added successfully'));
        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath('ecommage_raffle_tickets/raffleticket/addwinner', ['id' =>
                    $saveWinner->getId(), '_current' => true]);
        } else {
            return $resultRedirect->setPath('ecommage_raffle_tickets/raffleticket/index');
        }
    }
}
