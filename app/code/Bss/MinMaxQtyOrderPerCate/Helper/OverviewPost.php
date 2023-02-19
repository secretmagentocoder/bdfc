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
 * @category  BSS
 * @package   Bss_MinMaxQtyOrderPerCate
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MinMaxQtyOrderPerCate\Helper;

use Bss\MinMaxQtyOrderPerCate\Helper\OverviewPostFactory;
use Bss\MinMaxQtyOrderPerCate\Helper\Data;
use Magento\Framework\App\Helper\Context;

class OverviewPost extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var null
     */
    private $paymentRateLimiter;

    /**
     * @var OverviewPostFactory
     */
    private $overviewPostFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * OverviewPost constructor.
     *
     * @param Context $context
     * @param \Bss\MinMaxQtyOrderPerCate\Helper\OverviewPostFactory $overviewPostFactory
     */
    public function __construct(
        Context $context,
        OverviewPostFactory $overviewPostFactory,
        Data $helper,
        $paymentRateLimiter = null
    ) {
        $this->paymentRateLimiter = $paymentRateLimiter;
        $this->helper = $helper;
        $this->overviewPostFactory = $overviewPostFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Checkout\Api\PaymentProcessingRateLimiterInterface|null
     */
    public function getPaymentRateLimiterObject()
    {
        if ($this->helper->versionCompare('2.4.1')) {
            return $this->overviewPostFactory->create($this->paymentRateLimiter);
        }
        return null;
    }
}
