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

class CalculatorFactory
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
   * Calculate Discount
   *
   * @param \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $subject
   * @param \Closure $proceed
   * @param [type] $type
   * @return mixed
   */
    public function aroundCreate(
        \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $subject,
        \Closure $proceed,
        $type
    ) {
        $rules = $this->rulesDataHelper->getDiscountTypes();
       // $logger = $this->_objectManager->create(\Webkul\SpecialPromotions\Logger\Logger::class);
       
        if (isset($rules[$type])) {
            $path = $this->rulesDataHelper->getFilePath($type);
            // $logger->info("type".$path);
            return $this->_objectManager->create($path);
        } else {
            return $proceed($type);
        }
    }
}
