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
namespace Bss\MinMaxQtyOrderPerCate\Observer;

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Bss\MinMaxQtyOrderPerCate\Helper\Data;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\CategoryFactory;

class MinMaxQty implements ObserverInterface
{
    /**
     * SessionFactory
     *
     * @var SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Data
     */
    protected $minmaxHelper;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * MinMaxQty constructor.
     * @param SessionFactory $customerSessionFactory
     * @param Http $request
     * @param ManagerInterface $messageManager
     * @param Data $minmaxHelper
     * @param Cart $cart
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        SessionFactory $customerSessionFactory,
        Http $request,
        ManagerInterface $messageManager,
        Data $minmaxHelper,
        Cart $cart,
        CategoryFactory $categoryFactory
    ) {
        $this->customerSessionFactory = $customerSessionFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->minmaxHelper = $minmaxHelper;
        $this->cart = $cart;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Excute action
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->minmaxHelper->getConfig('enable')) {
            $items_name = [];
            $qty_categories = [];
            $customerGroupId = 0;
            $quoteItems = $this->cart->getQuote()->getAllVisibleItems();
            //validate for checkout with multishipping
            if ($this->request->getFullActionName() === 'multishipping_checkout_overviewPost') {
                $order = $observer->getEvent()->getOrder();
                $quoteItems = $order->getAllItems();
            }
            foreach ($quoteItems as $item) {
                $categories = $item->getProduct()->getCategoryIds();
                $items_name[$item->getName()] = $categories;
                $itemQty = $item->getQty();
                //get qty of multishipping items
                if ($this->request->getFullActionName() === 'multishipping_checkout_overviewPost') {
                    $itemQty = (float) $item->getData('qty_ordered');
                }
                foreach ($categories as $cate) {
                    if (is_array($qty_categories) && !empty($qty_categories[$cate])
                        && $this->request->getFullActionName() !== 'multishipping_checkout_overviewPost'
                    ) {
                        $qty_categories[$cate] += $itemQty;
                    } else {
                        $qty_categories[$cate] = $itemQty;
                    }
                }
            }
            $customer = $this->customerSessionFactory->create();
            if ($customer->isLoggedIn()) {
                $customerGroupId = $customer->getCustomer()->getGroupId();
            }
            $this->minmaxHelper->validateMinMaxQty($qty_categories, $items_name, $customerGroupId);
        }
    }
}
