<?php
namespace Custom\Recently\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderExtensionFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;

class Badges implements ProductRenderCollectorInterface
{
    /** SKU html key */
    const KEY = "badges";

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
        $product_is_new = $_product->getData('product_is_new');
        $bestseller_product = $_product->getData('bestseller_product');
        $product_in_offer = $_product->getData('product_in_offer');
        $product_in_travel = $_product->getData('product_in_travel');
        
        $product_badges_html = '';
        $product_badges_html .= '<div class="product-badges">';
            $sql = "SELECT * FROM `product_badges` ORDER BY `badge_id` ASC";
            $results = $connection->fetchAll($sql);
            // print_r($results);
            foreach ($results as $key => $value) {
                $badge_id = $value['badge_id'];
                $badge_slug = $value['badge_slug'];
                $badge_name = $value['badge_name'];

                $product_is_badges = $_product->getData($badge_slug);
                if ($product_is_badges == true) {
                    $product_badges_html .= '<div class="badges '.$badge_slug.'">';
                        $product_badges_html .= '<img src="/images/badges/'.$badge_slug.'.png" alt="'.$badge_name.'" />';
                    $product_badges_html .= '</div>';
                }
            }
        $product_badges_html .= '</div>';

        $extensionAttributes->setBadges($product_badges_html);

        $productRender->setExtensionAttributes($extensionAttributes);
    }
}