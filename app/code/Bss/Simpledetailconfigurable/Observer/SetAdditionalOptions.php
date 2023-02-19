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
 * @package   Bss_Simpledetailconfigurable
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetAdditionalOptions implements ObserverInterface
{
    /**
     * Url for custom option download controller
     * @var string
     */
    protected $_customOptionDownloadUrl = 'sales/download/downloadCustomOption';

    /**
     * @var string|null
     */
    protected $_formattedOptionValue = null;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $_serialize;

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    private $configurationPool;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * Url
     *
     * @var \Magento\Catalog\Model\Product\Option\UrlBuilder
     */
    protected $_urlBuilder;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $moduleConfig;

    /**
     * SetAdditionalOptions constructor.
     * @param \Magento\Framework\Serialize\Serializer\Json $serialize
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Catalog\Model\Product\Option\UrlBuilder $urlBuilder
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $serialize,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\Product\Option\UrlBuilder $urlBuilder,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
    ) {
        $this->_serialize = $serialize;
        $this->configurationPool = $configurationPool;
        $this->productRepository = $productRepository;
        $this->_escaper = $escaper;
        $this->_urlBuilder = $urlBuilder;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Magento\Checkout\Model\Cart $cart
         */
        $cart = $observer->getCart();
        $items = $cart->getQuote()->getAllItems();
        $itemCount = $cart->getItemsCount();
        $lastProductId = $cart->getCheckoutSession()->getLastAddedProductId();
        $lastAddedItem = $cart->getCheckoutSession()->getQuote()->getLastAddedItem();
        $lastChildProduct = $cart->getCheckoutSession()->getLastAddedChildProductId();
        if (!empty($items) && $itemCount > 0) {
            if ($lastProductId == null && $lastAddedItem != null) {
                $lastProductId = $lastAddedItem->getProductId();
            }
            $product = $this->productRepository->getById($lastProductId);
            if ($product->getId()) {
                /** @var Item $item */
                foreach ($items as $item) {
                    if ($item->getChildren()) {
                        $key = count($item->getChildren()) - 1;
                        /** @var \Magento\Quote\Model\Quote\Item $child */
                        $child = $item->getChildren()[$key];
                        $additionalOptions = $this->configurationPool
                            ->getByProductType($child->getProductType())
                            ->getOptions($child);
                        $this->saveItemOptionsBss($item, $additionalOptions, $child, $lastChildProduct);
                    }
                }
            }
        }
    }

    /**
     * @param $item
     * @param $additionalOptions
     * @param $child
     * @param $lastChildProduct
     * @throws LocalizedException
     */
    protected function saveItemOptionsBss($item, $additionalOptions, $child, $lastChildProduct)
    {
        if (!empty($additionalOptions) && $child->getProduct()->getId() == $lastChildProduct) {
            $options = $child->getProduct()->getProductOptionsCollection();

            /** @var \Magento\Catalog\Model\Product\Option $option */
            foreach ($options as $option) {
                $this->getAdditionalOptions($child, $additionalOptions, $option);
            }
            $option = [
                'product_id' => $item->getProductId(),
                'code' => 'additional_options',
                'value' => $this->_serialize->serialize($additionalOptions)
            ];
            $item->addOption($option)->saveItemOptions();
        }
    }

    /**
     * @param $child
     * @param $additionalOptions
     * @param $option
     * @throws LocalizedException
     */
    protected function getAdditionalOptions($child, $additionalOptions, $option)
    {
        if ($option->getType() == 'file') {
            $fileInfo = $child->getOptionByCode('option_' . $option->getId());
            if ($fileInfo && $fileInfo->getValue()) {
                if (is_array($fileInfo->getValue())) {
                    $fileInfoVal = $this->_serialize->serialize($fileInfo->getValue());
                } elseif (is_string($fileInfo->getValue())) {
                    $fileInfoVal = $fileInfo->getValue();
                }
                $additionalOption = [
                    "label" => $option->getTitle(),
                    "value" => $this->getFormattedOptionValue($fileInfoVal, $option, $child),
                    "print_value" => $this->getLabel($fileInfoVal),
                    "option_id" => $option->getId(),
                    "option_type" => "file",
                    "custom_view" => true
                ];

                foreach ($additionalOptions as $idxOption => $additionalOptionItem) {
                    if (isset($additionalOptionItem['option_id'])
                        && $additionalOptionItem['option_id'] == $option->getId()) {
                        // Remove old option and add new
                        unset($additionalOptions[$idxOption]);
                        break;
                    }
                }
                $additionalOptions[] = $additionalOption;
            }
        }
    }

    /**
     * @param array $fileInfoVal
     * @return string
     */
    protected function getLabel($fileInfoVal)
    {
        $value = $this->_serialize->unserialize($fileInfoVal);
        return $value['title'];
    }

    /**
     * @param string $optionValue
     * @param array $option
     * @param Item $item
     * @return string|null
     * @throws LocalizedException
     */
    public function getFormattedOptionValue($optionValue, $option, $item)
    {
        if ($this->_formattedOptionValue === null) {
            $value = $this->_serialize->unserialize($optionValue);
            if ($value === null) {
                return $optionValue;
            }
            $customOptionUrlParams = [
                'id' => $item->getOptionByCode('option_' . $option->getId())->getId(),
                'key' => $value['secret_key']
            ];

            $value['url'] = ['route' => $this->_customOptionDownloadUrl, 'params' => $customOptionUrlParams];

            $this->_formattedOptionValue = $this->_getOptionHtml($value);
        }
        return $this->_formattedOptionValue;
    }

    /**
     * @param string $optionValue
     * @return string
     * @throws LocalizedException
     */
    protected function _getOptionHtml($optionValue)
    {
        $value = $this->_unserializeValue($optionValue);
        try {
            $sizes = $this->prepareSize($value);

            $urlRoute = !empty($value['url']['route']) ? $value['url']['route'] : '';
            $urlParams = !empty($value['url']['params']) ? $value['url']['params'] : '';
            $title = !empty($value['title']) ? $value['title'] : '';

            return sprintf(
                '<a href="%s" target="_blank">%s</a> %s',
                $this->_getOptionDownloadUrl($urlRoute, $urlParams),
                $this->_escaper->escapeHtml($title),
                $sizes
            );
        } catch (\Exception $e) {
            throw new LocalizedException(__('The file options format is invalid. Use a correct format and try again.'));
        }
    }

    /**
     * Create a value from a storable representation
     *
     * @param string|array $value
     * @return array
     */
    protected function _unserializeValue($value)
    {
        if (is_array($value)) {
            return $value;
        } elseif (is_string($value) && !empty($value)) {
            return $this->_serialize->unserialize($value);
        } else {
            return [];
        }
    }

    /**
     * @param array $value
     * @return string
     */
    protected function prepareSize($value)
    {
        $sizes = '';
        if (!empty($value['width']) && !empty($value['height']) && $value['width'] > 0 && $value['height'] > 0) {
            $sizes = $value['width'] . ' x ' . $value['height'] . ' ' . __('px.');
        }
        return $sizes;
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _getOptionDownloadUrl($route, $params)
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }
}
