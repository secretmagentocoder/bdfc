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
namespace Bss\MinMaxQtyOrderPerCate\Helper;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Serialize\Serializer
     */
    protected $json;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * Data constructor.
     * @param Context $context
     * @param Http $request
     * @param ManagerInterface $messageManager
     * @param Cart $cart
     * @param \Magento\Framework\Serialize\Serializer $json
     * @param \Magento\Framework\Registry $registry
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        Context $context,
        Http $request,
        Cart $cart,
        ManagerInterface $messageManager,
        CategoryFactory $categoryFactory,
        \Magento\Framework\Registry $registry,
        ProductMetadataInterface $productMetadata,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        parent::__construct($context);
        $this->json = $json;
        $this->request = $request;
        $this->cart = $cart;
        $this->messageManager = $messageManager;
        $this->categoryFactory = $categoryFactory;
        $this->productMetadata = $productMetadata;
        $this->registry = $registry;
    }

    /**
     * Get Config
     *
     * @param $key
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->scopeConfig->getValue(
            'minmaxqtypercate/bssmmqpc/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $categories
     * @param $customerGroupId
     * @param $extremum
     * @return array
     */
    public function validateMinMax($categories, $customerGroupId, $extremum)
    {
        $minMaxQtyConfig = $this->getConfig('min_max_qty');
        if ($minMaxQtyConfig) {
            $extrema = $this->json->unserialize($minMaxQtyConfig);
            $categoriesId = array_keys($categories);
            $result = [];
            if (!empty($extrema)) {
                foreach ($extrema as $value) {
                    if (array_key_exists('customer_group_id',$value) && $value['customer_group_id'] == $customerGroupId &&
                        in_array($value['category_id'], $categoriesId)) {
                        $qty = $categories[$value['category_id']];
                        if ($extremum == 'min' && $qty < (float)$value['min_sale_qty']) {
                            $result[$value['category_id']] = $value['min_sale_qty'];
                        }
                        if ($value['max_sale_qty'] && $extremum == 'max' && $qty > (float)$value['max_sale_qty']) {
                            $result[$value['category_id']] = $value['max_sale_qty'];
                        }
                    }
                }
            }
            return $result;
        }
        return [];
    }

    /**
     * Get Max Qty
     *
     * @param array $categories
     * @param int $customer
     * @return array
     */
    public function validate($categories, $customerGroupId)
    {
        $result['min'] = $this->validateMinMax($categories, $customerGroupId, 'min');
        $result['max'] = $this->validateMinMax($categories, $customerGroupId, 'max');
        return $result;
    }

    /**
     * @param $qty_categories
     * @param $items_name
     * @param $customerGroupId
     */
    public function validateMinMaxQty($qty_categories, $items_name, $customerGroupId)
    {
        $mes = '';
        $validationQty = $this->validate($qty_categories, $customerGroupId);
        if (!empty($validationQty['min']) || !empty($validationQty['max'])) {
            if ($this->request->getFullActionName() === 'checkout_cart_index'
                || $this->request->getFullActionName() === 'multishipping_checkout_overviewPost'
            ) {
                foreach ($validationQty as $extremum => $qty_limit) {
                   $mes = $this->showMessage($items_name, $qty_limit, $extremum);
                }
            }
            $this->cart->getQuote()->setHasError(true);
            $this->cart->getQuote()->setMessageErrorCategory($mes);
        }
    }

    /**
     * @param $items_name
     * @param $limit
     * @param $extremum
     */
    protected function showMessage($items_name, $limit, $extremum)
    {
        $mess_config = $this->getConfig('mess_err_max');
        if ($extremum == 'min') {
            $mess_config = $this->getConfig('mess_err_min');
        }
        $message = [];
        foreach ($limit as $categoryId => $qty) {
            $product_names = [];
            foreach ($items_name as $item_name => $categoriesId) {
                if (in_array($categoryId, $categoriesId)) {
                    $product_names[] = $item_name;
                }
            }
            $productName = implode(',', $product_names);
            $cateName = $this->loadCateId($categoryId);
            $message = str_replace("{{category_name}}", $cateName, $mess_config);
            $message = str_replace("{{qty_limit}}", $qty, $message);
            $message = str_replace("{{product_name}}", $productName, $message);
            //through message with multi shipping
            if ($this->request->getFullActionName() === 'multishipping_checkout_overviewPost') {
                $this->registry->register('bss_message', $message);
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($message)
                );
            }
            $this->messageManager->addErrorMessage($message);
        }
        return $message;
    }

    /**
     * @param $categoryId
     * @return string
     */
    protected function loadCateId($categoryId)
    {
        return $this->categoryFactory->create()->load($categoryId)->getName();
    }

    /**
     * @param $ver
     * @param string $operator
     * @return boolean
     */
    public function versionCompare($ver, $operator = '>=')
    {
        $version = $this->productMetadata->getVersion();
        return version_compare($version, $ver, $operator);
    }
}
