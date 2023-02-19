<?php

namespace Flamingo\Checkout\Plugin\Controller\Account;

class LoginPost
{
    public function __construct
    (
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->_customerSession = $customerSession;
        $this->url = $url;
        $this->request = $request;
    }

    public function afterExecute(\Magento\Customer\Controller\Account\LoginPost $subject, $result)
    {
        $data = $this->request->getParam('login');
        if (! empty ($data)) {
		if (array_key_exists('type',$data) && !empty($this->_customerSession->isLoggedIn()))
		{
		    $result->setUrl($this->url->getUrl('change-delivery-date'));
		}
        }

        return $result;
    }
}
