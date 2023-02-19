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
namespace Webkul\SpecialPromotions\Plugin\Model;

use Magento\Quote\Model\Quote\Item\AbstractItem;

class ValidatorUtility
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Webkul\SpecialPromotions\Helper\Data
     *
     * */
    protected $promotionsHelper;

    /**
     * Initialize
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Webkul\SpecialPromotions\Helper\Data $rulesDataHelper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\SpecialPromotions\Helper\Data $rulesDataHelper
    ) {
        $this->_objectManager = $objectManager;
        $this->rulesDataHelper = $rulesDataHelper;
    }

    /**
     * Checking if we can process the rule
     *
     * This plugin is for the condition when module is disabled
     *
     * @param \Magento\SalesRule\Model\Utility $subject
     * @param Callable $proceed
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return bool
     */
    public function aroundCanProcessRule(
        \Magento\SalesRule\Model\Utility $subject,
        $proceed,
        $rule,
        $address
    ) {
        $isEnable = $this->rulesDataHelper->checkModuleStatus();
        $rules = $this->rulesDataHelper->getDiscountTypes();
        $type = $rule->getSimpleAction();
        if (!$isEnable && isset($rules[$type])) {
            return false;
        }
        return $proceed($rule, $address);
    }
}
