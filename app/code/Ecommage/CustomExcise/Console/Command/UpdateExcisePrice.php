<?php

namespace Ecommage\CustomExcise\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Psr\Log\LoggerInterface;

class UpdateExcisePrice extends Command
{

    protected $logger;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productCollectionFactory;


    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        State $state,
        LoggerInterface $logger,
        string $name = null
    ) {
        $this->logger = $logger;
        $this->state = $state;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('ecommage:update:updateExcisePrice');
        $this->setDescription('This is my console command run in update excise price!');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $arrivalStoreId = 2;
        $productCollection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('type_id', ['neq' => 'virtual'])
            ->addStoreFilter($arrivalStoreId);

        foreach ($productCollection as $product) {
            try {
                if ($product->getData('excise_duty')) {
                    $productUpdate = $this->productRepository->getById($product->getId(), true, $arrivalStoreId);

                    $productUpdate->setPrice($productUpdate->getPrice() * 2);
                    // $productUpdate->setExciseDutyPrice($productUpdate);

                    $productUpdate->save();
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                continue;
            }
        }
    }
}
