<?php

namespace Flamingo\Checkout\Plugin;

class ConfigProviderPlugin extends \Magento\Framework\Model\AbstractModel
{

    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {

        $items = $result['totalsData']['items'];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $home_url = $storeManager->getStore()->getBaseUrl();
        $storeId = $storeManager->getStore()->getId();

        for($i=0;$i<count($items);$i++){

            $quoteId = $items[$i]['item_id'];
            $quote = $objectManager->create('\Magento\Quote\Model\Quote\Item')->load($quoteId);
            $productId = $quote->getProductId();
            $product = $objectManager->create('\Magento\Catalog\Model\Product')->load($productId);

            $product_sku = $product->getSku();  
            $items[$i]['product_sku'] = $product_sku;

            $product_size = $product->getResource()->getAttribute('size')->getFrontend()->getValue($product);
            $items[$i]['product_size'] = $product_size;

            $product_options = $this->quote_item_options($quoteId);
            $items[$i]['product_options'] = $product_options;

            // $product_brand = $product->getResource()->getAttribute('product_brand')->getFrontend()->getValue($product);
            $product_brand = $product->getData('product_brand');
            $product_brand_arr = explode(",", $product_brand);
            $product_brand_html = '';
            $count = '0';
            if (!empty($product_brand_arr)) {
                foreach ($product_brand_arr as $value) {
                    if (!empty($value)) {
                        if ($count != '0') {
                            $product_brand_html .= ', ';
                        }
                        $brand_id = $value;
                        $query = $connection->select()->from('magetop_brand', ['*'])->where('brand_id = ?', $brand_id);
                        $query_result = $connection->fetchRow($query);
                        $brand_name = $query_result['name'];
                        $brand_url_key = $query_result['url_key'];
                        $brand_url = $home_url.'brand/'.$brand_url_key.'.html';
                        $product_brand_html .= '<a href="'.$brand_url.'" title="'.$brand_name.'">'.$brand_name.'</a>';
                    }
                    $count++;
                }
            }
            $items[$i]['product_brand'] = $product_brand_html;

            $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data'); 

            $product_price = $priceHelper->currency($product->getFinalPrice(), true, false);

            $product_price_html = '';
            if ($product->hasSpecialPrice()):
                $product_price_html .= '<span class="price-discount">';

                    $_price = $product->getPrice();
                    $_finalPrice = $product->getFinalPrice();
                    if($_finalPrice < $_price) {
                        $_savingPercent = 100 - round(($_finalPrice / $_price)*100);
                        $_savingPrice = $_price - $_finalPrice;
                        $savingPrice = number_format((float)$_savingPrice, 2, '.', '');

                        $product_price_html .= '<span class="price-container">';
                            $product_price_html .= '<span class="price">';
                                $product_price_html .= 'Save '.$_savingPercent.'%';
                            $product_price_html .= '</span>';
                        $product_price_html .= '</span>';
                    }
                $product_price_html .= '</span>';

                $product_price_html .= '<del class="old-price">';
                    $product_price = $priceHelper->currency($product->getPrice(), true, false);
                    $product_price_html .= '<span class="price-wrapper"><span class="price">'.$product_price.'</span></span>';
                $product_price_html .= '</del>';

                $product_price_html .= '<span class="special-price">';
                    $product_price = $priceHelper->currency($product->getFinalPrice(), true, false);
                    $product_price_html .= '<span class="price-wrapper"><span class="price">'.$product_price.'</span></span>';
                $product_price_html .= '</span>';
            else:
                $product_price_html .= '<span class="regular-price">';
                    $product_price = $priceHelper->currency($product->getFinalPrice(), true, false);
                    $product_price_html .= '<span class="price-wrapper"><span class="price">'.$product_price.'</span></span>';
                $product_price_html .= '</span>';
            endif;

            $items[$i]['product_price'] = $product_price_html;

            $gift_message_id = $quote->getGiftMessageId();
            if ($gift_message_id == null) {
                $is_gift_message = "no";
            }else {
                $is_gift_message = "yes";
            }
            $items[$i]['is_gift_message'] = $is_gift_message;

            $gift_wrap_id = $quote->getGwId();
            if ($gift_wrap_id == null) {
                $is_gift_wrap = "no";
            }else {
                $is_gift_wrap = "yes";
            }
            $items[$i]['is_gift_wrap'] = $is_gift_wrap;
            // print_r($quote);
        }

        $result['totalsData']['items'] = $items;
        return $result;
    }

    public function quote_item_options($item_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
        // $itemsVisible = $cart->getQuote()->getAllVisibleItems();
        // $items = $cart->getQuote()->getAllItems();
        $session = $objectManager->get('\Magento\Checkout\Model\Session');
        $quote_repository = $objectManager->get('\Magento\Quote\Api\CartRepositoryInterface');
        $qid = $session->getQuoteId();
        if (empty($qid)) {
            return '';
        }
        $quote = $quote_repository->get($qid);      
        $items = $quote->getAllItems();
        
        $product_options = '';
        foreach($items as $item) {
            if ($item->getId() == $item_id) {
                $attribute = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Eav\Attribute');
                $product = $objectManager->create('\Magento\Catalog\Model\Product')->load($item->getProductId());
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                if (!empty($options)) {
                    if (isset($options['info_buyRequest']['super_attribute'])) {
                        $super_attribute = $options['info_buyRequest']['super_attribute'];
                        $product_options .= '<dl class="item-options">';
                        foreach ($super_attribute as $key => $value) {
                            $attribute_id = $key;
                            $attribute_option_id = $value;

                            $attribute_model = $attribute->load($attribute_id);
                            $attribute_code = $attribute_model->getAttributeCode();
                            $attribute_label = $attribute_model->getFrontendLabel();

                            $_attributeId = $product->getResource()->getAttribute($attribute_code);
                            if ($_attributeId->usesSource()) {
                                $attribute_option_text = $_attributeId->getSource()->getOptionText($attribute_option_id);
                            }
                            $product_options .= '<dt>'.$attribute_label.'</dt>';
                            $product_options .= '<dd>'.$attribute_option_text.'</dd>';

                        }
                        $product_options .= '</dl>';
                    }
                }
            }
        }

        return $product_options;
    }

}