<?php
namespace Custom\Recently\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderExtensionFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;

class Brand implements ProductRenderCollectorInterface
{
    /** SKU html key */
    const KEY = "brand";

    /**
     * @var ProductRenderExtensionFactory
     */
    private $productRenderExtensionFactory;

    /**
     * Sku constructor.
     * @param ProductRenderExtensionFactory $productRenderExtensionFactory
     */
    public function __construct(
        ProductRenderExtensionFactory $productRenderExtensionFactory
    ) {
        $this->productRenderExtensionFactory = $productRenderExtensionFactory;
    }

    /**
     * @inheritdoc
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        $extensionAttributes = $productRender->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->productRenderExtensionFactory->create();
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $home_url = $storeManager->getStore()->getBaseUrl();

        $_product = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
        // $product_brand = $_product->getResource()->getAttribute('product_brand')->getFrontend()->getValue($_item);
        $product_brand = $_product->getData('product_brand');
        $product_brand_arr = explode(",", $product_brand);
        
        $product_brand_html = '';
        $product_brand_html .= '<div class="product-brand">';
        $count = '0';
        if (!empty($product_brand_arr)) {
            foreach ($product_brand_arr as $value) {
                if (!empty($value)) {
                    if ($count != '0') {
                        $product_brand_html .= ', ';
                    }
                    $brand_id = $value;
                    $query = $connection->select()->from('magetop_brand', ['*'])->where('brand_id = ?', $brand_id);
                    $result = $connection->fetchRow($query);
                    $brand_name = $result['name'];
                    $brand_url_key = $result['url_key'];
                    $brand_url = $home_url.'brand/'.$brand_url_key.'.html';
                    $product_brand_html .= '<a href="'.$brand_url.'" title="'.$brand_name.'">'.$brand_name.'</a>';
                }
                $count++;
            }
        }
        $product_brand_html .= '</div>';

        $extensionAttributes->setBrand($product_brand_html);

        $productRender->setExtensionAttributes($extensionAttributes);
    }
}