<?php

namespace Ecommage\NavSyncPromotion\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ecommage\NavSyncPromotion\Helper\Data;

class NavSync extends Command
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * NavSync constructor.
     *
     * @param Data        $helper
     * @param string|null $name
     */
    public function __construct(
        Data $helper,
        string $name = null
    ) {
        $this->helper = $helper;
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("nav:sync:promotion");
        $this->setDescription("This command will immediately sync promotion from sap to magento.");
        parent::configure();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->helper->setOutput($output)
                     ->navSyncOffer();
        $output->writeln("<comment>Finished running the command</comment>");
    }
}
