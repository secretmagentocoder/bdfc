<?php
namespace Ecommage\CustomerCategory\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\Widget\Html\Pager;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\CatalogWidget\Model\Rule;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Rule\Model\Condition\Combine;
use Magento\Rule\Model\Condition\Sql\Builder as SqlBuilder;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;

/**
 * Catalog Products List widget block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class ProductsList extends \Magento\CatalogWidget\Block\Product\ProductsList
{
    /**
     * Prepare and return product collection
     *
     * @return Collection
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     * @throws LocalizedException
     */
    public function createCollection()
    {
        $collection = parent::createCollection();
        $value = $this->getRequest()->getParam('raffle_type');
        if ($value) {
            $collection->addAttributeToFilter('raffle_type', $value);
        }
        return $collection;
    }

}
