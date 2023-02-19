<?php
namespace Rootways\Megamenu\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class MenuLayouts extends Template
{
    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $categoryRepository;
    
    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_categoryHelper;
    
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $categoryFlatConfig;
    
    /**
     * @var \Magento\Theme\Block\Html\Topmenu
     */
    protected $topMenu;
    
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;
    
    /**
     * @var \Rootways\Megamenu\Helper\Data
     */
    protected $_customhelper;
    
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;
    
    /**
     * @var \Rootways\Megamenu\Model\Category\DataProvider\Plugin
     */
    protected $customCatImage;
    
    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;

    /** @var CategoryCollectionFactory */
    protected $categoryCollectionFactory;

    /** @var StoreManagerInterface */
    protected $storeManager;

    protected $_categoryCollection;

    protected $_baseUrl;
    
    /**
     * Main3 Block.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState
     * @param \Magento\Theme\Block\Html\Topmenu $topMenu
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Rootways\Megamenu\Helper\Data $helper
     * @param \Rootways\Megamenu\Model\Category\DataProvider\Plugin $customCatImage
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Theme\Block\Html\Topmenu $topMenu,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Rootways\Megamenu\Helper\Data $helper,
        \Rootways\Megamenu\Model\Category\DataProvider\Plugin $customCatImage,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        CategoryCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->_categoryHelper = $categoryHelper;
        $this->categoryFlatConfig = $categoryFlatState;
        $this->topMenu = $topMenu;
        $this->_filterProvider = $filterProvider;
        $this->_customhelper = $helper;
        $this->_customcatimage = $customCatImage;
        $this->_customresourceConfig = $resourceConfig;
        $this->_objectManager = $objectManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }
    
    function getMultiTabbing($viewAll = null, $childrenCategories, $navCnt0)
    {
        if ($viewAll == null) {
            $main_cat = $this->categoryRepository->get($category->getId(), $this->_customhelper->getStoreId());
            $colnum = (int)$main_cat->getMegamenuTypeSubcatlevel();
            $viewMoreAfter = $main_cat->getMegamenuTypeViewmore();
            if ($colnum == '0') {
                $colnum = 4;
            }
        } else {
            $colnum = 4;
            //$navCnt0 check this logic for All Categories section.
        }
        
        //if ($childrenCategories = $this->getChildCategories($category)) {
            $catHtml = '<div class="megamenu fullmenu clearfix fourcoltab multitabcol_'.$colnum.'">';
                $catHtml.= '<div class="mainmenuwrap clearfix">';
                    $catHtml .= '<ul class="colultabone">';
                        $navCnt = 0;
                        foreach ($childrenCategories as $childCategory) {
                            $navCnt++;
                            $load_cat = $this->getLoadedCat($childCategory);
							if ($viewAll != null) {
								$main_cat = $load_cat;
							}
                            //$catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.' category-item clearfix"><a class="clearfix" style="line-height:'.$main_cat->getMegamenuShowCatimageHeight().'px;" href='.$load_cat->getURL().'>';
                            $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.' category-item clearfix"><a class="clearfix" href='.$load_cat->getURL().'>';
                                $catHtml .= $this->getImageHtml($main_cat, $load_cat, 2);
                                $catHtml .= '<em>'.$load_cat->getName().'</em></a>';
                                if ($colnum >= 2) {
                                    $childrenCategories_2 = $load_cat->getChildrenCategories();
                                    if (count($childrenCategories_2)) {
                                        $catHtml .= '<span class="rootmenu-click"><i class="rootmenu-arrow"></i></span>';
                                        $catHtml .= '<ul class="colultabtwo clearfix ">';
                                            $navCnt1 = 0;
                                            foreach ($childrenCategories_2 as $childCategory2) {
                                                $navCnt1++;
                                                $load_cat_sub = $this->categoryRepository->get($childCategory2->getId(), $this->_customhelper->getStoreId());
                                                //$catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.'-'.$navCnt1.' category-item clearfix"><a class="clearfix" style="line-height:'.$main_cat->getMegamenuShowCatimageHeight().'px;" href='.$load_cat_sub->getURL().'>';
                                                $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.'-'.$navCnt1.' category-item clearfix"><a class="clearfix" href='.$load_cat_sub->getURL().'>';
                                                    $catHtml .= $this->getImageHtml($main_cat, $load_cat_sub, 2);
                                                    $catHtml .= '<em>'.$load_cat_sub->getName().'</em></a>';
                                                    if ($colnum >= 3) {
                                                        $childrenCategories_3 = $load_cat_sub->getChildrenCategories();
                                                        if (count($childrenCategories_3)) {
                                                            $catHtml .= '<span class="rootmenu-click"><i class="rootmenu-arrow"></i></span>';
                                                            $catHtml .= '<ul class="colultabthree">';
                                                                $navCnt2 = 0;
                                                                foreach ($childrenCategories_3 as $childCategory3) {
                                                                    $navCnt2++;
                                                                    $load_cat_sub_2 = $this->categoryRepository->get($childCategory3->getId(), $this->_customhelper->getStoreId());
                                                                    //$catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.'-'.$navCnt1.'-'.$navCnt2.' category-item clearfix"><a class="clearfix" style="line-height:'.$main_cat->getMegamenuShowCatimageHeight().'px;" href='.$load_cat_sub_2->getURL().'>';
                                                                    $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.'-'.$navCnt1.'-'.$navCnt2.' category-item clearfix"><a class="clearfix" href='.$load_cat_sub_2->getURL().'>';
                                                                    $catHtml .= $this->getImageHtml($main_cat, $load_cat_sub_2, 2);
                                                                    $catHtml .= '<em>'.$load_cat_sub_2->getName().'</em></a>';
                                                                    if ($colnum >= 4) {
                                                                        
                                                                        $childrenCategories_4 = $load_cat_sub_2->getChildrenCategories();
                                                                        if (count($childrenCategories_4)) {
                                                                            $catHtml .= '<span class="rootmenu-click"><i class="rootmenu-arrow"></i></span>';
                                                                            $catHtml .= '<div class="resultdiv clearfix">';
                                                                                $subCatCnt = 0;
                                                                                foreach ($childrenCategories_4 as $childCategory4) {
                                                                                    $load_cat_sub_3 = $this->categoryRepository->get($childCategory4->getId(), $this->_customhelper->getStoreId());
                                                                                    /*
                                                                                    if ($subCatCnt >= $viewMoreAfter && $viewMoreAfter != '') {
                                                                                        $catHtml .= '<div class="root-col-1"><a class="view-more" href='.$load_cat_sub_2->getURL().'>'.__('View More').'</a></div>';
                                                                                        break;
                                                                                    }
                                                                                    */
                                                                                    $subCatCnt++;
                                                                                    $catHtml .= '<div class="root-col-3">';
                                                                                         if ($main_cat->getMegamenuShowCatimage() == 1) {
                                                                                         //if (1 == 1) {
                                                                                            if ($this->_customhelper->getMegaMenuImageName($load_cat_sub_3) != '') {
                                                                                                $imageurl = $this->_customhelper->getMegaMenuImageUrl($load_cat_sub_3);
                                                                                            } else {
                                                                                                $imageurl = $this->getViewFileUrl('Rootways_Megamenu::images/rootimgicon.jpg');
                                                                                            }
                                                                                            $catHtml .= ' <span class="productbtmimg"><img src='.$imageurl.' alt="'.$load_cat_sub_3->getName().'"/></span>';	
                                                                                        }
                                                                                        $catHtml .=  '<a href="'.$load_cat_sub_3->getURL().'" class="productbtmname">'.$load_cat_sub_3->getName().'</a>';
                                                                                     $catHtml .= '</div>';
                                                                                }
                                                                            $catHtml .= '</div>';
                                                                        }
                                                                    }
                                                                    $catHtml .= '</li>';

                                                                }
                                                            $catHtml .= '</ul>';
                                                        }
                                                    }
                                                $catHtml .= '</li>'; 
                                            }
                                        $catHtml .= '</ul>';
                                     } else {
                                        $catHtml .= '<div class="colultabonenofound clearfix">Sub-category not found for '.$load_cat->getName().' Category</div>';
                                    }
                                }
                            $catHtml .= '</li>';
                        }
                    $catHtml .= '</ul>';
                $catHtml .= '</div>';
            $catHtml .= '</div>';
        //}
		return $catHtml;
    
    }
    
    public function getLoadedCat($childCategory)
    {
        if (gettype($childCategory) == 'object') {
            $load_cat = $this->categoryRepository->get($childCategory->getId(), $this->_customhelper->getStoreId());
        } else {
            $load_cat = $this->categoryRepository->get($childCategory, $this->_customhelper->getStoreId());
        }
        
        return $load_cat;
    }
}
