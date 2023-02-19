<?php
/**
 * Webkuls Software.
 *
 * @category  Webkuls
 * @package   Webkuls_SpecialPromotions
 * @author    Webkuls
 * @copyright Copyright (c) Webkuls Software Private Limited (https://Webkuls.com)
 * @license   https://store.Webkuls.com/license.html
 */
namespace Webkuls\SpecialPromotions\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DisableMpAuction
 */
class DisablePromotion extends Command
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    /**
     * @var \Magento\Framework\Module\Status
     */
    protected $_modStatus;

    /**
     * Initialize
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Eav\Model\Entity\Attribute $entityAttribute
     * @param \Magento\Framework\Module\Status $modStatus
     * @param \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider $valueProvider
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Framework\Module\Status $modStatus,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider $valueProvider
    ) {
        $this->_resource = $resource;
        $this->_moduleManager = $moduleManager;
        $this->_eavAttribute = $entityAttribute;
        $this->_modStatus = $modStatus;
        $this->valueProvider = $valueProvider;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('specialpromotions:disable')
            ->setDescription('SpecialPromotions Disable Command');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rulesArray = $this->valueProvider->getSimpleAction();
        $comma = "";
        $rulesString = "";
        foreach ($rulesArray as $rules) {
            if (is_array($rules['value'])) {
                foreach ($rules['value'] as $rule) {
                    $rulesString .= $comma . "'" . $rule['value'] . "'";
                    $comma = ",";
                }
            }
        }

        if ($this->_moduleManager->isEnabled('Webkuls_SpecialPromotions')) {
            $connection = $this->_resource
                ->getConnection();

            // rules disabled
            $salesrule = $this->_resource->getTableName(
                'salesrule',
                \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
            );
            $connection->query("UPDATE " . $salesrule . " SET is_active = '0' WHERE simple_action IN(" .
                $rulesString . ")");

            // disable auction module
            $this->_modStatus->setIsEnabled(false, ['Webkuls_SpecialPromotions']);

            // delete entry from setup_module table

            $tableName = $this->_resource->getTableName('setup_module');
            $connection->delete(
                $tableName,
                ['module = ?' => 'Webkuls_SpecialPromotions']
            );
            $output->writeln('<info>SpecialPromotion module has been disabled successfully.</info>');
        } else {
            $output->writeln(
                "<info>SpecialPromotion module already disabled.For enabled this modules, run 'setup:upgrade'</info>"
            );
        }
    }
}
