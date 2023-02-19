<?php
namespace Sparsh\MobileNumberLogin\Block\Form;

/**
 * Class Login
 * @package Sparsh\MobileNumberLogin\Block\Form
 */
class Login extends \Magento\Customer\Block\Form\Login
{
    /**
     * @var \Sparsh\MobileNumberLogin\Helper\Data
     */
    private $helperData;

    /**
     * Login constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Sparsh\MobileNumberLogin\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Sparsh\MobileNumberLogin\Helper\Data $helperData,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $customerUrl, $data);
        $this->helperData = $helperData;
    }

    /**
     * Retrieve login mode
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLoginMode()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        return $this->helperData->getLoginMode($storeId);
    }
}
