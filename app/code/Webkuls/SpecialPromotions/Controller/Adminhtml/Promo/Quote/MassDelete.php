<?php
/**
 * Webkuls Software.
 *
 * @category  Webkuls
 * @package   Webkuls_SpecialPromotions
 * @author    Webkuls Software Private Limited
 * @copyright Copyright (c) Webkuls Software Private Limited (https://Webkuls.com)
 * @license   https://store.Webkuls.com/license.html
 */


namespace Webkuls\SpecialPromotions\Controller\Adminhtml\Promo\Quote;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\SalesRule\Api\RuleRepositoryInterface;

class MassDelete extends Action
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;
/**
 * Initialize
 *
 * @param Action\Context $context
 * @param RuleRepositoryInterface $ruleRepository
 */
    public function __construct(
        Action\Context $context,
        RuleRepositoryInterface $ruleRepository
    ) {
        parent::__construct($context);

        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var int[]|null $ids */
        $ids = $this->getRequest()->getParam('ids');
        $resultRedirect = $this->resultRedirectFactory->create();

        if (is_array($ids)) {
            try {
                foreach ($ids as $ruleId) {
                    $this->ruleRepository->deleteById($ruleId);
                }

                $this->messageManager->addSuccessMessage(__('You deleted %1 rule(s).', count($ids)));

                return  $resultRedirect->setPath('sales_rule/*/');
            } catch (LocalizedException $exception) {
                $this->messageManager->addExceptionMessage($exception);
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('We can\'t delete the rule right now. Please review the log and try again.')
                );
            }
            $resultRedirect->setPath('sales_rule/*/');
            return$resultRedirect;
            
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a rule(s) to delete.'));
        $resultRedirect = $this->resultRedirectFactory->create();

        return  $resultRedirect->setPath('sales_rule/*/');
    }
}
