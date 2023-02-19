<?php

namespace Amasty\Orderattr\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class RecurringData implements InstallDataInterface
{
    /**
     * @var \Amasty\Orderattr\Model\Indexer\ActionProcessor
     */
    private $indexProcessor;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $config;

    public function __construct(
        \Amasty\Orderattr\Model\Indexer\ActionProcessor $indexProcessor,
        \Magento\Eav\Model\Config $config
    ) {

        $this->indexProcessor = $indexProcessor;
        $this->config = $config;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     *
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (!$context->getVersion() || version_compare($context->getVersion(), '3.0.0', '<')) {
            $this->config->clear();
            $this->indexProcessor->reindexAll();
        }
    }
}
