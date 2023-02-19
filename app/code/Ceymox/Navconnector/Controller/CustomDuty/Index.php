<?php declare(strict_types=1);
/**
 * @package Ceymox_Navconnector
 */
namespace Ceymox\Navconnector\Controller\CustomDuty;

use Magento\Framework\App\Action\Context;
use Ceymox\Navconnector\Model\CustomDuty\CustomDutyManager;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var CustomDutyManager
     */
    private $customDutyManager;

    /**
     * Constructor function
     *
     * @param Context $context
     * @param CustomDutyManager $customDutyManager
     */
    public function __construct(
        Context $context,
        CustomDutyManager $customDutyManager
    ) {
        $this->customDutyManager = $customDutyManager;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return void
     */
    public function execute()
    {
        $this->customDutyManager->getCustomDuty();
    }
}
