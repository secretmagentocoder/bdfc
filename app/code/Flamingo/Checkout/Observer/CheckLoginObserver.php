<?php
namespace Flamingo\Checkout\Observer;
use Magento\Framework\Controller\ResultFactory; 

class CheckLoginObserver implements \Magento\Framework\Event\ObserverInterface
{
	 public function __construct(     
        \Magento\Checkout\Helper\Cart $cartHelper,
		\Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
		\Magento\Customer\Model\Session $customerSession 
    )
	{
		$this->customerSession  = $customerSession; 
        $this->cartHelper = $cartHelper;
		$this->responseFactory = $responseFactory;
        $this->url = $url;
    }
	
	public function execute(\Magento\Framework\Event\Observer $observer)
	{

		 if ($this->cartHelper->getItemsCount() === 0) {
			 	$redirectionUrl = $this->url->getUrl('checkout/cart/index');
				$this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
          return $this;
		}
		else{
			
			if($this->customerSession->isLoggedIn()) {
			   return $this;
			}
			else{
				
				$redirectionUrl = $this->url->getUrl('checkoutlogin/index/index');
				$this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
				exit(0);
				//return $this;
			}
		}
			
	}
}