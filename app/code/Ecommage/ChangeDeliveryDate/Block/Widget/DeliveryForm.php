<?php

namespace Ecommage\ChangeDeliveryDate\Block\Widget;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class DeliveryForm extends Template implements BlockInterface
{
    const CACHE_DELIVERY_DATE = 'CACHE_DELIVERY_DATE';
    /**
     * @var string
     */
    protected $_template = "widget/change-delivery-date.phtml";
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var Context
     */
    protected $httpContext;
    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * DeliveryForm constructor.
     *
     * @param Session          $customerSession
     * @param Template\Context $context
     * @param array            $data
     */
    public function __construct(
        FormKey $formKey,
        Session $customerSession,
        Template\Context $context,
        Context $httpContext,
        array $data = []
    ) {
        $this->formKey         = $formKey;
        $this->httpContext     = $httpContext;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return Phrase
     */
    public function getTitle()
    {
        $title = $this->getData('title');
        if (empty($title)) {
            $title = 'Collection Date Change';
        }

        return __($title);
    }

    /**
     * @return bool
     */
    public function isLogin()
    {
        return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * @return string
     */
    public function getMobileNo()
    {
        $customerData = $this->customerSession->getCustomerData();
        if ($customerData && $customerData->getCustomAttribute('mobile_number')) {
            return $customerData->getCustomAttribute('mobile_number')->getValue();
        }

        return '';
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCountryCode()
    {
        $customerData = $this->customerSession->getCustomerData();
        if ($customerData && $customerData->getCustomAttribute('country_code')) {
            return $customerData->getCustomAttribute('country_code')->getValue();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('change_sc/status/update', ['_secure' => true]);
    }

    /**
     * Get block cache life time
     *
     * @return int|bool|null
     */
    protected function getCacheLifetime()
    {
        return parent::getCacheLifetime();
    }
}

