<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MinMaxQtyOrderPerCate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MinMaxQtyOrderPerCate\Block\Adminhtml\System\Config\Form;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magento\Customer\Model\GroupFactory;

class Customergroup extends Select
{
    /**
     * Method List
     *
     * @var array
     */
    protected $groupfactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param GroupFactory $groupfactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        GroupFactory $groupfactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->groupfactory = $groupfactory;
    }

     /**
      * To Option Array
      *
      * @return array
      */
    public function toOptionArray()
    {
        if (!$this->getOptions()) {
            $customerGroupCollection = $this->groupfactory->create()->getCollection();
            $cOptions[] = [
                'label' => $this->escapeHtml('Please Select a Customer Group'),
                'value' => ''
            ];
            foreach ($customerGroupCollection as $customerGroup) {
                 $cOptions[] = [
                     'label' => $this->escapeHtml($customerGroup->getCustomerGroupCode()),
                     'value' => $customerGroup->getCustomerGroupId()
                 ];
            }
        }
        return $cOptions;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $options =  $this->toOptionArray();
        foreach ($options as $option) {
            $this->addOption($option['value'], $option['label']);
        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
