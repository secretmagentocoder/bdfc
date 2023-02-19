<?php
/**
 * 
 * @package Bdfc_General
 */
namespace Bdfc\General\Rewrite\Magetop_Brand;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
class Brandpage extends \Magetop\Brand\Block\Brandpage
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magetop\Brand\Helper\Data
     */
    protected $_brandHelper;

    /**
     * @var \Magetop\Brand\Model\Brand
     */
    protected $_brand;

    private $productCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context      
     * @param \Magento\Framework\Registry                      $registry     
     * @param \Magetop\Brand\Helper\Data                           $brandHelper  
     * @param \Magetop\Brand\Model\Brand                           $brand        
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager 
     * @param array                                            $data         
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magetop\Brand\Helper\Data $brandHelper,
        \Magetop\Brand\Model\Brand $brand,
        CollectionFactory $productCollectionFactory,
        array $data = []
        ) {
        $this->_brand = $brand;
        $this->productCollectionFactory = $productCollectionFactory;    
        $this->_coreRegistry = $registry;
        $this->_brandHelper = $brandHelper;
        parent::__construct($context, $registry,$brandHelper, $brand, $data);
    }

    public function _construct()
    {
        if (!$this->getConfig('general_settings/enable')) return;
        parent::_construct();

        $store = $this->_storeManager->getStore();
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addStoreFilter($store->getId());
        $productCollection->addAttributeToSelect('product_brand');
        $brands = [];
        foreach ($productCollection as $col) {
            $brand = $col->getProductBrand();
            if ( $brand && (! in_array($brand, $brands))) {
                $brands[] = $brand;
            }
        }        

        $brand = $this->_brand;
        $brandCollection = $brand->getCollection()
                ->addFieldToFilter('status',1)
                ->addStoreFilter($store)
                ->addFieldToFilter('brand_id',['in'=>$brands])
                ->setOrder('position','ASC');
        $this->setCollection($brandCollection);

        $template = '';
        $layout = $this->getConfig('brand_list_page/layout');
        if ($layout == 'grid') {
            $template = 'Magetop_Brand::brandlistpage_grid.phtml';
        } else {
            $template = 'Magetop_Brand::brandlistpage_list.phtml';
        }
        if(!$this->hasData('template')){
            $this->setTemplate($template);
        }
    }


    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $collection = $this->getCollection();
        $toolbar = $this->getToolbarBlock();
        $store = $this->_storeManager->getStore();
        // set collection to toolbar and apply sort
        if($toolbar){
            $itemsperpage = (int)$this->getConfig('brand_list_page/item_per_page',$store->getId());
            $toolbar->setData('_current_limit',$itemsperpage)->setCollection($collection);
            $this->setChild('toolbar', $toolbar);
        }
        return parent::_beforeToHtml();
    }
}