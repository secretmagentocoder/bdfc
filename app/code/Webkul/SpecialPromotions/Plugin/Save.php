<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SpecialPromotions
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SpecialPromotions\Plugin;

/**
 * Metadata provider for sales rule edit form.
 */
class Save extends \Magento\Framework\DataObject
{
    /**
     * Initialize
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Webkul\SpecialPromotions\Helper\Data $rulesDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\SpecialPromotions\Helper\Data $rulesDataHelper,
        array $data = []
    ) {
        $this->request = $request;
        $this->rulesDataHelper = $rulesDataHelper;
        parent::__construct($data);
    }
     
    /**
     * Save
     *
     * @param \Magento\SalesRule\Controller\Adminhtml\Promo\Quote $subject
     * @param \Closure $proceed
     * @return void
     */
    public function aroundExecute(
        \Magento\SalesRule\Controller\Adminhtml\Promo\Quote $subject,
        \Closure $proceed
    ) {
        $isEnable = $this->rulesDataHelper->checkModuleStatus();
        if ($isEnable) {
            $ruleConditionForPercentage = [
                'buy_x_get_n_percent_discount',
                'most_expensive',
                'cheapest',
                'each_n_percent_discount',
                'each_product_aft_nth_percent',
                'group_n_percent_discount' ,
                'product_set_percent'
            ];
            if ($subject->getRequest()->getPostValue()) {
                $data = $subject->getRequest()->getPostValue();
                if (isset($data['simple_action']) && in_array($data['simple_action'], $ruleConditionForPercentage)) {
                    if ($data['discount_amount'] > 100) {
                        $data['discount_amount'] = 100;
                    }
                }
                $subject->getRequest()->setPostValue($data);
            }
            $proceed();
        } else {
            $proceed();
        }
    }
}
