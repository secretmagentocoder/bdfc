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
namespace Webkul\SpecialPromotions\Model\Condition;

class Customer extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerModel;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    
    /**
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Customer $customerModel,
        array $data = []
    ) {
        $this->customerSession = $session;
        $this->customerModel = $customerModel;
        parent::__construct($context, $data);
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            "website_id"        => ("Associate to Website") ,
            "store_id"          => ("Create In") ,
            "created_at"        => ("Created At") ,
            "id"                => ("Customer ID") ,
            "dob"               => ("Date of Birth") ,
            "firstname"         => ("First Name") ,
            "lastname"          => ("Last Name") ,
            "email"             => ("Email") ,
            "gender"            => ("Gender") ,
            "membership_days"   => ("Membership Days") ,
            "middlename"        => ("Middle Name/Initial") ,
            "prefix"            => ("Name Prefix") ,
            "suffix"            => ("Name Suffix") ,
            "taxvat"            => ("Tax/VAT Number") ,
            "updated_at"        => ("Updated At") ,
            
        ];
            $this->setAttributeOption($attributes);
            
            return $this;
    }

    /**
     * Get attribute element
     *
     * @return $this
     */
    public function getAttributeElement()
    {
            $element = parent::getAttributeElement();
            $element->setShowAsText(true);
            return $element;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'shipping_method':
            case 'weight':
            case 'total_qty':
                return 'numeric';
            case 'payment_method':
            case 'country_id':
            case 'base_subtotal':
            case 'region_id':
                return 'select';
        }

        return 'string';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'payment_method':
            case 'country_id':
            case 'shipping_method':
            case 'region_id':
                return 'select';
        }
        
        return 'text';
    }

    /**
     * Get value select options
     *
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'shipping_method':
                    $options = $this->_shippingAllmethods->toOptionArray();
                    break;

                case 'payment_method':
                    $options = $this->_paymentAllmethods->toOptionArray();
                    break;
                
                case 'region_id':
                    $options = $this->_directoryAllregion->toOptionArray();
                    break;

                case 'country_id':
                    $options = $this->_directoryCountry->toOptionArray();
                    break;

                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $customer = $model;
        if (!$customer instanceof \Magento\Customer\Model\Customer) {
            if ($this->customerSession->isLoggedIn()) {
                $customer = $this->customerModel->load($this->customerSession->getId());
            }
        }
        return parent::validate($customer);
    }
}
