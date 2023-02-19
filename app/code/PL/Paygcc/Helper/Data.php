<?php
/**

@Author paygcc.com contact info@paygcc.com

 */
namespace PL\Paygcc\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $jsonHelper;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->jsonHelper = $jsonHelper;
    }

    public function getTransactionDetails($response = [])
    {
        $paymentInfo = [];
        if (isset($response['paymnet_requests'][0]['status'])) {
            $paymentInfo['status'] = $response['paymnet_requests'][0]['status'];
        }
        if (isset($response['paymnet_requests'][0]['order_id'])) {
            $paymentInfo['order_id'] = $response['paymnet_requests'][0]['order_id'];
        }
        if (isset($response['paymnet_requests'][0]['payment_response']['tnx_id'])) {
            $paymentInfo['tnx_id'] = $response['paymnet_requests'][0]['payment_response']['tnx_id'];
        }
        if (isset($response['paymnet_requests'][0]['payment_response']['receipt'])) {
            $paymentInfo['receipt'] = $response['paymnet_requests'][0]['payment_response']['receipt'];
        }
        if (isset($response['paymnet_requests'][0]['payment_response']['authorizationCode'])) {
            $paymentInfo['authorizationCode'] = $response['paymnet_requests'][0]['payment_response']['authorizationCode'];
        }

        if (isset($response['paymnet_requests'][0]['payment_response']['number'])) {
            $paymentInfo['number'] = $response['paymnet_requests'][0]['payment_response']['number'];
        }
        if (isset($response['paymnet_requests'][0]['payment_response']['meta'])) {
            $meta = $response['paymnet_requests'][0]['payment_response']['meta'];
            $metaData = $this->jsonHelper->jsonDecode($meta);
            if (isset($metaData['order']['status'])) {
                $paymentInfo['order_status'] = $metaData['order']['status'];
            }
            if (isset($metaData['result'])) {
                $paymentInfo['result'] = $metaData['result'];
            }

        }
        return $paymentInfo;

    }
}
