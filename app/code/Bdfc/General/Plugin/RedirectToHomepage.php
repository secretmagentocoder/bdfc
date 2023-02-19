<?php
/**
 *
 * @package Bdfc_General
 */

namespace Bdfc\General\Plugin;

use Magento\Cms\Controller\Noroute\Index;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlRewrite;
use Magento\Framework\App\ResponseInterface;

class RedirectToHomepage
{
    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlRewrite
     */
    private $urlRewrite;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param ForwardFactory           $resultForwardFactory
     * @param RequestInterface         $request
     * @param StoreManagerInterface    $storeManager
     * @param UrlRewrite               $urlRewrite
     * @param ResponseInterface        $response
     */
    public function __construct(
        ForwardFactory $resultForwardFactory,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        UrlRewrite $urlRewrite,
        ResponseInterface $response
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
         $this->request = $request;
         $this->storeManager = $storeManager;
         $this->urlRewrite = $urlRewrite;
         $this->response = $response;
    }

   /**
    * 404 Redirect
    *
    * @param Index $subject
    * @param array $result
    * @return array
    */
    public function afterexecute(Index $subject, $result)
    {
        $requestValue = $this->request->getRequestURI();
        $requestedPath = '';
        if ($requestValue) {
            $value = explode("/", (strstr($requestValue, '?', true)));
            if (!empty($value)) {
                if (isset($value[0]) && isset($value[1])) {
                    unset($value[0]);
                    unset($value[1]);
                    $requestedPath = implode("/", $value);
                }
            }
        }
        if ($requestedPath) {
        $url = $this->urlRewrite->getCollection()->addFieldToFilter('request_path', $requestedPath)->getFirstItem();
        $entityType = $url['entity_type'];
        $entityId = $url['entity_id'];
        if ($entityType == 'product' || $entityType == 'category') {
            $homePageUrl  = $this->storeManager->getStore()->getBaseUrl();
            $this->response->setRedirect($homePageUrl, 301)->sendResponse();
            }
        } else {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->setController('index');
            $resultForward->forward('defaultNoRoute');
            return $resultForward;
        }
    }
}
