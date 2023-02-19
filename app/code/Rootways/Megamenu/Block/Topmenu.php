<?php
/**
 * Mega Menu HTML Block.
 *
 * @category  Site Search & Navigation
 * @package   Rootways_Megamenu
 * @author    Developer RootwaysInc <developer@rootways.com>
 * @copyright 2021 Rootways Inc. (https://www.rootways.com)
 * @license   Rootways Custom License
 * @link      https://www.rootways.com/pub/media/extension_doc/license_agreement.pdf
 */

namespace Rootways\Megamenu\Block;

class Topmenu extends MenuLayouts
{
    public function getStoreBaseUrl()
    {
        if(!$this->_baseUrl) {
            $this->_baseUrl = $this->storeManager->getStore()->getBaseUrl();
        }

        return $this->_baseUrl;
    }

    public function getCategoryCollection()
    {
        if (!$this->_categoryCollection) {
            $categories = $this->categoryCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('is_active', ['eq'=>1])
                ->addAttributeToFilter('include_in_menu', ['eq'=>1])
                ->setStore($this->_customhelper->getStoreId());

            $categoryCollection = array();
            foreach($categories as $maincat) {
                $categoryCollection[$maincat['entity_id']] = $maincat->debug();
            }
            $this->_categoryCollection = $categoryCollection;
        }

        return $this->_categoryCollection;
    }

    private function getUrlPath($urlPath)
    {
        return $this->getStoreBaseUrl() . $urlPath . $this->_customhelper->getCategoryUrlSuffix();
    }

    /**
     * Support for get attribute value with HTML
     */
    public function getBlockContent($content = '')
    {
        if (!$this->_filterProvider) {
            return $content;
        }
        return $this->_filterProvider->getBlockFilter()->filter(trim($content));
    }
   
    /**
     * Check if current page is home
     */
    public function getIsHomePage()
    {
        $currentUrl = $this->getUrl('', ['_current' => true]);
        $urlRewrite = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $currentUrl == $urlRewrite;
    }
    
    /**
     * Return categories helper
     */
    public function getCategoryHelper()
    {
        return $this->_categoryHelper;
    }
    
    /**
     * Return top menu html
     */
    public function getHtml()
    {
        return $this->topMenu->getHtml();
    }
    
    /**
     * Retrieve current store categories
     */
    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
    }
    
    /**
     * Retrieve child store categories
     */
    public function getChildCategories($category)
    {
        $children = [];
        if ($this->categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource()) {
            $subcategories = (array)$category->getChildrenNodes();
        } else {
            $subcategories = $category->getChildren();
        }
        foreach ($subcategories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }
            $children[] = $category;
        }
        return $children;
    }
    
    /**
     * Simple Mega Menu HTML Block.
     */
    public function simpletMenu($category, $navCnt0)
    {
        $main_cat = $this->categoryRepository->get($category->getId(), $this->_customhelper->getStoreId());
		$catHtml = '';
        // 2nd Level Category
        if ($childrenCategories = $this->getChildCategories($category)) {
            $dropdown_pos = '';
            if ($main_cat->getMegamenuTypeHalfPos() == 1) {
               $dropdown_pos .= ' dropdown-leftside';
            }
			$catHtml .= '<ul class="rootmenu-submenu '.$dropdown_pos.'">';
            $navCnt = 0;
            foreach ($childrenCategories as $childCategory) {
                $navCnt++;
                $load_cat = $this->categoryRepository->get($childCategory->getId(), $this->_customhelper->getStoreId());
			    $collection_sub = $this->getChildCategories($childCategory);
                if (count($collection_sub)) {
                    $arrow = '<span class="cat-arrow"></span>';
                } else { 
                    $arrow = '';
                }
				$catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.' category-item"><a href="'.$load_cat->getURL().'">'.$childCategory->getName().$arrow;
                if ( $load_cat->getMegamenuTypeLabeltx() != '' ) {
                    $catHtml .= '<span class="top-sub-label"><em class="rootmenutag" style="background-color: #'.$load_cat->getMegamenuTypeLabelclr().'">'.$load_cat->getMegamenuTypeLabeltx().'</em></span>';
                }
                $catHtml .= '</a>';
                    
                    // 3rd Level Category
					if (count($collection_sub)) {
						$catHtml .= '<ul class="rootmenu-submenu-sub">';
                            $navCnt1 = 0;
							foreach ($collection_sub as $childCategory2) {
                                $navCnt1++;
                                $load_cat_sub = $this->categoryRepository->get($childCategory2->getId(), $this->_customhelper->getStoreId());
								$collection_sub_sub = $this->getChildCategories($childCategory2);
                                if (count($collection_sub_sub)) {
                                    $arrow = '<span class="cat-arrow"></span>';
                                } else {
                                    $arrow = '';
                                }
                                $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.'-'.$navCnt1.' category-item"><a href="'.$load_cat_sub->getURL().'">'.$childCategory2->getName().$arrow;
                                if ( $load_cat_sub->getMegamenuTypeLabeltx() != '' ) {
                                    $catHtml .= '<span class="top-sub-label"><em class="rootmenutag" style="background-color: #'.$load_cat_sub->getMegamenuTypeLabelclr().'">'.$load_cat_sub->getMegamenuTypeLabeltx().'</em></span>';
                                }
                                $catHtml .= '</a>';
                                // 4th Level Category
                                if (count($collection_sub_sub)) {
									$catHtml .= ' <ul class="rootmenu-submenu-sub-sub">';
                                        $navCnt2 = 0;
										foreach ($collection_sub_sub as $childCategory3) {
                                            $navCnt2++;
                                            $load_sub_3 = $this->categoryRepository->get($childCategory3->getId(), $this->_customhelper->getStoreId());
                                            $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.'-'.$navCnt1.'-'.$navCnt2.' category-item"><a href="'.$load_sub_3->getURL().'">'.$childCategory3->getName();
                                            if ( $load_sub_3->getMegamenuTypeLabeltx() != '' ) {
                                                $catHtml .= '<span class="top-sub-label"><em class="rootmenutag" style="background-color: #'.$load_sub_3->getMegamenuTypeLabelclr().'">'.$load_sub_3->getMegamenuTypeLabeltx().'</em></span>';
                                            }
                                            $catHtml .= '</a></li>';
										 }
									$catHtml .= '</ul>';
								}
								$catHtml .= '</li>';
							 }
						$catHtml .= '</ul>';
					}
				$catHtml .= '</li>';
			}
			$catHtml .= '</ul>';
		}
		return $catHtml;
	}
    
    /**
     * Mega Dropdown HTML Block.
     */
    public function megadropdown($category, $navCnt0)
	{
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$categoryHelper = $this->getCategoryHelper();
        $main_cat = $_objectManager->create('Magento\Catalog\Model\Category')->load($category->getId());
        $main_cat->getCollection()->addAttributeToFilter('include_in_menu', '1');
        
        $catHtml = '';
        
        $colnum = 2;
        /*
        $colnum = $main_cat->getMegamenuTypeNumofcolumns();
		if ($colnum == 0) {
            $colnum = 2;
        }
        */
        
        $left_width = $main_cat->getMegamenuTypeLeftblockW();
        $right_width = $main_cat->getMegamenuTypeRightblockW();
        $cat_width = 12 - ($left_width + $right_width);
        $category_area_width = 'root-sub-col-'.$cat_width;
        $left_content_area = 'root-sub-col-'.$left_width;
        $right_content_area = 'root-sub-col-'.$right_width;
        
		if ($childrenCategories = $this->getChildCategories($category)) {
            $catHtml .= '<div class="verticalmenu02 clearfix">';
            $catHtml .= '<ul class="vertical-list clearfix">';
                foreach ($childrenCategories as $childCategory) {
                    $load_cat = $_objectManager->create('Magento\Catalog\Model\Category')->load($childCategory->getId());
                    $load_cat->getCollection()->addAttributeToFilter('include_in_menu', '1');
                    
                    $catHtml .= '<li class="rootverticalnav">';
                        //$catHtml .= '<a href="'.$load_cat->getURL().'">'.$load_cat->getName().'</a>';
                        $catHtml .= '<a href="'.$load_cat->getURL().'">';
                            if ($this->_customhelper->getMegaMenuImageName($load_cat) != '') {
                                $catHtml .= '<span class="main-category-name">';
                                    $catHtml .= '<i class="main-category-icon"><img src="'.$this->_customhelper->getMegaMenuImageUrl($load_cat).'"/></i>';
                                    $catHtml .= '<em>'.$load_cat->getName().'</em>';
                                $catHtml .= '</span>';
                            } else {
                                $catHtml .= $load_cat->getName();
                            }
                        $catHtml .= '</a>';
                        if ($childrenCategories_2 = $this->getChildCategories($childCategory)) {
                            $catHtml .= '<div class="v_halfmenu varticalmenu_main halfwidth clearfix">';
                            //$catHtml .= '<div class="vertical_fullwidthmenu varticalmenu_main fullwidth clearfix">';
                            $catHtml .= '<div class="root-col-1 clearfix">';
                                //$catHtml .= '<div class="root-sub-col-3 clearfix"><img src="http://magento2demo.rootways.com/pub/media/catalog/category/party-blazers.jpg" alt="Dropdown Title"></div>';
                                $catWitdh = 6;
                                if ($load_cat->getData('megamenu_type_leftblock') != '') {
                                    $catHtml .= '<div class="root-sub-col-3 clearfix">';
                                        $catHtml .= $this->getBlockContent($load_cat->getData('megamenu_type_leftblock'));
                                    $catHtml .= '</div>';   
                                } else {
                                    $catWitdh = $catWitdh + 3;
                                }
                                if ($load_cat->getData('megamenu_type_rightblock') == '') {
                                    $catWitdh = $catWitdh + 3;
                                }
                                $catHtml .= '<div class="root-sub-col-'.$catWitdh.' clearfix">';
                                    $cnt = 1;
                                    $cat_tot = count($childrenCategories);
                                    $brk = ceil($cat_tot/$colnum);
                                    // 2nd Level Category
                                    $navCnt = 0;
                                    foreach ($childrenCategories_2 as $childCategory2) {
                                        $catHtml .= '<div class="root-col-'.count($childrenCategories_2).' clearfix">';
                                        $navCnt++;
                                        $load_cat_sub = $_objectManager->create('Magento\Catalog\Model\Category')->load($childCategory2->getId());
                                        $load_cat_sub->getCollection()->addAttributeToFilter('include_in_menu', '1');

                                        if ($main_cat->getMegamenuShowCatimage() == 1) {
                                            if ($this->_customhelper->getMegaMenuImageName($load_cat_sub) != '') {
                                                $imageurl = $this->_customhelper->getMegaMenuImageUrl($load_cat_sub);
                                            } else {
                                                $imageurl = $this->getViewFileUrl('Rootways_Megamenu::images/rootimgicon.jpg');
                                            }
                                            $image_html = '<span class="vertical-listing-img"><img style="width:'.$main_cat->getMegamenuShowCatimageWidth().'px; height:'.$main_cat->getMegamenuShowCatimageHeight().'px;" src='.$imageurl.' alt=""/></span>';
                                            $line_height =  'style="line-height:'.$main_cat->getMegamenuShowCatimageHeight().'px;"';

                                            $catHtml .= '<a href='.$load_cat_sub->getURL().' class="catproductimg"><img width='.$main_cat->getMegamenuShowCatimageWidth().' height='.$main_cat->getMegamenuShowCatimageHeight().' src='.$imageurl.' alt="'.$main_cat->getName().'"/></a>';

                                        }
                                        $catHtml .= '<div class="title nav-'.$navCnt0.'-'.$navCnt.' category-item"><a href='.$load_cat_sub->getURL().'>'.$load_cat_sub->getName().'</a></div>';

                                        if( $childrenCategories_3 = $this->getChildCategories($childCategory2) ) {
                                            $catHtml .= '<ul class="level3-popup halfwidth-popup-sub-sub">';
                                                $navCnt1 = 0;
                                                foreach ($childrenCategories_3 as $childCategory3) {
                                                    $navCnt1++;
                                                    $load_cat_sub2 = $_objectManager->create('Magento\Catalog\Model\Category')->load($childCategory3->getId());
                                                    $load_cat_sub2->getCollection()->addAttributeToFilter('include_in_menu', '1');
                                                    if ($main_cat->getMegamenuShowCatimage() == 1) {
                                                        if ($this->_customhelper->getMegaMenuImageName($load_cat_sub2) != '') { 
                                                            $imageurl_sub = $this->_customhelper->getMegaMenuImageUrl($load_cat_sub2);
                                                        } else {
                                                            $imageurl_sub = $this->getViewFileUrl('Rootways_Megamenu::images/rootimgicon.jpg');
                                                        }
                                                        $image_html_sub = '<img style="width:25px; height:25px;" src='.$imageurl_sub.' alt=""/>';
                                                    } else { 
                                                        $image_html_sub = ''; 
                                                    }

                                                    $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.'-'.$navCnt1.' category-item"><a class="clearfix" href='.$load_cat_sub2->getURL().'>';
                                                    $catHtml .= $image_html_sub;
                                                    $catHtml .= '<span class="level3-name">'.$load_cat_sub2->getName().'</span>';
                                                    $catHtml .= '</a></li>';
                                                }
                                            $catHtml .= '</ul>';
                                        }
                                        $cnt ++;
                                        $catHtml .= '</div>';
                                    }
                                $catHtml .= '</div>';
                                //$catHtml .= '<div class="root-sub-col-3 clearfix"><img src="http://magento2demo.rootways.com/pub/media/catalog/category/hoodies-for-men.jpg" alt=""></div>';
                                if ($load_cat->getData('megamenu_type_rightblock') != '') {
                                    $catHtml .= '<div class="root-sub-col-3 clearfix">';
                                        $catHtml .= $this->getBlockContent($load_cat->getData('megamenu_type_rightblock'));
                                    $catHtml .= '</div>';   
                                }
                            $catHtml .= '</div>';
                            $catHtml .= '</div>';
                        }
                    
                    $catHtml .= '</li>';
                }
            $catHtml .= '</ul>';
            $catHtml .= '</div>';
		}
		return $catHtml;
	}
    
    /**
     * Dropdown with Title Menu HTML Block.
     */
    public function dropdownTitle($category, $navCnt0)
	{
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$categoryHelper = $this->getCategoryHelper();
        $main_cat = $_objectManager->create('Magento\Catalog\Model\Category')->load($category->getId());
        $main_cat->getCollection()->addAttributeToFilter('include_in_menu', '1');
        $catHtml = '';
        
        $colnum = 1;
        /*
        $colnum = $main_cat->getMegamenuTypeNumofcolumns();
		if ($colnum == 0) {
            $colnum = 1;
        }
        $left_width = $main_cat->getMegamenuTypeLeftblockW();
        $right_width = $main_cat->getMegamenuTypeRightblockW();
        $cat_width = 12 - ($left_width + $right_width);
        $category_area_width = 'root-sub-col-'.$cat_width;
        $left_content_area = 'root-sub-col-'.$left_width;
        $right_content_area = 'root-sub-col-'.$right_width;
		*/
        // 2nd Level Category
        if ($childrenCategories = $this->getChildCategories($category)) {
			$catHtml .= '<div class="halfmenu dropdowntitle clearfix">';
                if ( $main_cat->getMegamenuTypeHeader() != '' ) {
                    $catHtml .= '<div class="menuheader root-col-1 clearfix">';
                        $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_header'));
				    $catHtml .= '</div>';   
                }
                $catHtml .= '<div class="root-col-1 clearfix">';
                    
                    if ($this->_customhelper->getMegaMenuImageName($main_cat) != '') {
                        $main_imageurl = $this->_customhelper->getMegaMenuImageUrl($main_cat);
                    } else {
                        $main_imageurl = $this->getViewFileUrl('Rootways_Megamenu::images/rootimgicon.jpg');
                    }
                    $catHtml .= '<div class="root-sub-col-6 clearfix rootmegamenu_block">';
                        $catHtml .= '<img src='.$main_imageurl.' alt="'.$main_cat->getName().'"/>';
                        $catHtml .= '<div class="title"><a href="'.$main_cat->getURL().'">'.$main_cat->getName().'</a></div>';
                    $catHtml .= '</div>';
                    
                    $catHtml .= '<div class="root-sub-col-6 clearfix">';
                        $catHtml .= '<ul class="root-col-'.$colnum.' clearfix level2-popup">';
                        $cnt = 1;
                        $cat_tot = count($childrenCategories);
                        $brk = ceil($cat_tot/$colnum);
                        // 3rd Level Category
                        foreach ($childrenCategories as $childCategory) {
                            $load_cat = $_objectManager->create('Magento\Catalog\Model\Category')->load($childCategory->getId());
                            $load_cat->getCollection()->addAttributeToFilter('include_in_menu', '1');

                            if ( $main_cat->getMegamenuShowCatimage() == 1 ) {
                                if ($this->_customhelper->getMegaMenuImageName($load_cat) != '') {
                                    $imageurl = $this->_customhelper->getMegaMenuImageUrl($load_cat);
                                } else {
                                    $imageurl = $this->getViewFileUrl('Rootways_Megamenu::images/rootimgicon.jpg');
                                }	
                                $image_html = '<img style="width:'.$main_cat->getMegamenuShowCatimageWidth().'px; height:'.$main_cat->getMegamenuShowCatimageHeight().'px;" src='.$imageurl.' alt="'.$load_cat->getName().'"/>';
                            } else { 
                                $image_html = '';
                            }
                            
                            $catHtml .= '<li><a href='.$load_cat->getURL().'>';
                            $catHtml .= $image_html;
                            $catHtml .= '<span class="sub-cat-name" style="height:'.$main_cat->getMegamenuShowCatimageWidth().'px;">'.$load_cat->getName();
                            if ( $load_cat->getMegamenuTypeLabeltx() != '' ) {
                                $catHtml .= '<span class="top-sub-label"><em class="rootmenutag" style="background-color: #'.$load_cat->getMegamenuTypeLabelclr().'">'.$load_cat->getMegamenuTypeLabeltx().'</em></span>';
                            }
                            $catHtml .= '</span>';
                            if ( $childrenCategories_2 = $this->getChildCategories($childCategory) ) {
                                $catHtml .= '<span class="cat-arrow"></span>';
                            }
                            $catHtml .='</a>';
                            if ( $childrenCategories_2 = $this->getChildCategories($childCategory) ) {
                                $catHtml .= '<span class="rootmenu-click"><i class="rootmenu-arrow"></i></span>';
                            }
                            if( $childrenCategories_2 = $this->getChildCategories($childCategory) ){
                                $catHtml .= '<ul class="level3-popup halfwidth-popup-sub-sub">';
                                    foreach ( $childrenCategories_2 as $childCategory2 ) {
                                        //$load_cat_sub = $_objectManager->create('Magento\Catalog\Model\Category')->load($childCategory2->getId());
                                        //$catHtml .= '<li><a href='.$load_cat_sub->getURL().'>'.$load_cat_sub->getName().'</a></li>';
                                        
                                        $load_cat_sub = $_objectManager->create('Magento\Catalog\Model\Category')->load($childCategory2->getId());
                                        $load_cat_sub->getCollection()->addAttributeToFilter('include_in_menu', '1');
                                        if ($main_cat->getMegamenuShowCatimage() == 1) {
                                            if ($this->_customhelper->getMegaMenuImageName($load_cat_sub) != '' ) {
                                                $imageurl_sub = $this->_customhelper->getMegaMenuImageUrl($load_cat_sub);
                                            } else {
                                                $imageurl_sub = $this->getViewFileUrl('Rootways_Megamenu::images/rootimgicon.jpg');
                                            }
                                            $image_html_sub = '<img style="width:25px; height:25px;" src='.$imageurl_sub.' alt=""/>';
                                        } else { 
                                            $image_html_sub = ''; 
                                        }

                                        $catHtml .= '<li><a class="clearfix" href='.$load_cat_sub->getURL().'>';
                                        $catHtml .= $image_html_sub;
                                        $catHtml .= '<span class="level3-name">'.$load_cat_sub->getName();
                                        if ( $load_cat_sub->getMegamenuTypeLabeltx() != '' ) {
                                            $catHtml .= '<span class="top-sub-label"><em class="rootmenutag" style="background-color: #'.$load_cat_sub->getMegamenuTypeLabelclr().'">'.$load_cat_sub->getMegamenuTypeLabeltx().'</em></span>';
                                        }
                                        $catHtml .= '</span></a></li>';
                                    }
                                $catHtml .= '</ul>';
                            }

                            $catHtml .=  '</li>';
                            if ( $cnt%$brk == 0 && $cnt != $cat_tot ) { $catHtml .= '</ul><ul  class="root-col-'.$colnum.' clearfix level2-popup">'; }
                            $cnt ++;
                        }
                        $catHtml .= '</ul>';
                    $catHtml .= '</div>';
                $catHtml .= '</div>';
                if ( $main_cat->getMegamenuTypeFooter() != '' ) {
                    $catHtml .= '<div class="menufooter root-col-1 clearfix">';
                        $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_footer'));
                    $catHtml .= '</div>';   
                }  
			$catHtml .= '</div>';
		}
		return $catHtml;
	}
    
    /**
     * Half-Width Mega Menu HTML Block.
     */
    public function halfMenu($category, $navCnt0)
	{
        return $this->halfFullHtml($category, $navCnt0, 0);
	}
    
    public function halfFullHtml($category, $navCnt0, $isFullWidth)
	{
        $main_cat = $this->categoryRepository->get($category->getId(), $this->_customhelper->getStoreId());
        $colnum = $main_cat->getMegamenuTypeNumofcolumns();
		if ($colnum == 0) {
            $colnum = 2;
        }
        $left_width = $main_cat->getMegamenuTypeLeftblockW();
        $right_width = $main_cat->getMegamenuTypeRightblockW();
        $cat_width = 12 - ($left_width + $right_width);
        $category_area_width = 'root-sub-col-'.$cat_width;
        $left_content_area = 'root-sub-col-'.$left_width;
        $right_content_area = 'root-sub-col-'.$right_width;
        $catHtml = '';
		if ($childrenCategories = $this->getChildCategories($category)) {
            $mainWrapperClass = 'halfmenu clearfix';
            if ($isFullWidth == 1) {
                $mainWrapperClass = 'megamenu fullmenu clearfix linksmenu';
            }
			$catHtml .= '<div class="'.$mainWrapperClass.'">';
            $catHtml .= '<div class="'.$this->_customhelper->menuContainerClass().'">';
                //$catHtml .= '<span class="rw-dropdownclose"></span>';
                if ($main_cat->getMegamenuTypeHeader() != '') {
                    $catHtml .= '<div class="menuheader root-col-1 clearfix">';
                        $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_header'));
				    $catHtml .= '</div>';   
                }
                $catHtml .= '<div class="root-col-1 clearfix">';
                    if ($left_width != 0) {
                        $catHtml .= '<div class="'.$left_content_area.' clearfix rootmegamenu_block">';
                            $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_leftblock'));
                        $catHtml .= '</div>';   
                    }
                    $catHtml .= '<div class="'.$category_area_width.' clearfix">';
                        $catHtml .= '<ul class="root-col-'.$colnum.' clearfix level2-popup">';
                        $cnt = 1;
                        $cat_tot = count($childrenCategories);
                        $brk = ceil($cat_tot/$colnum);
                        // 2nd Level Category
                        $navCnt = 0;
                        foreach ($childrenCategories as $childCategory) {
                            $navCnt++;
                            $load_cat = $this->categoryRepository->get($childCategory->getId(), $this->_customhelper->getStoreId());
                            $image_html = $this->getImageHtml($main_cat, $load_cat, 1);
                            $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.' category-item"><a href='.$load_cat->getURL().'>';
                            $catHtml .= $image_html;
                            //$catHtml .= '<span class="level2-name sub-cat-name" style="height:'.$main_cat->getMegamenuShowCatimageHeight().'px;">'.$load_cat->getName() . '</span>';
                            $catHtml .= '<span class="level2-name sub-cat-name">'.$load_cat->getName() . '</span>';
                            if ($load_cat->getMegamenuTypeLabeltx() != '') {
                                $catHtml .= '<span class="top-sub-label"><em class="rootmenutag" style="background-color: #'.$load_cat->getMegamenuTypeLabelclr().'">'.$load_cat->getMegamenuTypeLabeltx().'</em></span>';
                            }
                            $childrenCategories_2 = $this->getChildCategories($childCategory);
                            if ($childrenCategories_2) {
                                $catHtml .= '<span class="cat-arrow"></span>';
                            }
                            $catHtml .= '</a>';
                            if ($childrenCategories_2) {
                                $catHtml .= '<span class="rootmenu-click"><i class="rootmenu-arrow"></i></span>';
                                $catHtml .= '<ul class="level3-popup">';
                                    $navCnt1 = 0;
                                    foreach ($childrenCategories_2 as $childCategory2) {
                                        $navCnt1++;
                                        $load_cat_sub = $this->categoryRepository->get($childCategory2->getId(), $this->_customhelper->getStoreId());
                                        $image_html_sub = $this->getImageHtml($main_cat, $load_cat_sub, 2);
                                        $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.'-'.$navCnt1.' category-item"><a href='.$load_cat_sub->getURL().'>';
                                        $catHtml .= $image_html_sub;
                                        $catHtml .= '<span class="level3-name sub-cat-name">'.$load_cat_sub->getName().'</span>';
                                        
                                        $childrenCategories_3 = $this->getChildCategories($childCategory2);
                                        if ($childrenCategories_3) {
                                            $catHtml .= '<span class="cat-arrow"></span>';
                                        }
                                        $catHtml .= '</a>';
                                        if ($childrenCategories_3) {
                                            $catHtml .= '<span class="rootmenu-click"><i class="rootmenu-arrow"></i></span>';
                                            $catHtml .= '<ul class="level4-popup">';
                                                $catHtml .= $this->getLastCatHtml($main_cat, $childrenCategories_3, $navCnt0, $navCnt, $navCnt1);
                                            $catHtml .= '</ul>';
                                        }
                                        $catHtml .= '</li>';
                                    }
                                $catHtml .= '</ul>';
                            }
                            $catHtml .=  '</li>';
                            if ($cnt%$brk == 0 && $cnt != $cat_tot) {$catHtml .= '</ul><ul class="root-col-'.$colnum.' clearfix level2-popup">';}
                            $cnt ++;
                        }
                        $catHtml .= '</ul>';
                    $catHtml .= '</div>';
                    if ($right_width != 0) {
                        $catHtml .= '<div class="'.$right_content_area.' clearfix rootmegamenu_block">';
                            $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_rightblock'));
                        $catHtml .= '</div>';
                    }
                $catHtml .= '</div>';
                if ($main_cat->getMegamenuTypeFooter() != '') {
                    $catHtml .= '<div class="menufooter root-col-1 clearfix">';
                        $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_footer'));
                    $catHtml .= '</div>';   
                }
			$catHtml .= '</div></div>';
		}
		return $catHtml;
	}
    
    public function halfFullContentOnlyHtml($category, $isFullWidth)
    {
        $main_cat = $this->categoryRepository->get($category->getId(), $this->_customhelper->getStoreId());
        $left_width = $main_cat->getMegamenuTypeLeftblockW();
        $right_width = $main_cat->getMegamenuTypeRightblockW();
        $left_content_area = 'root-sub-col-'.$left_width;
        $right_content_area = 'root-sub-col-'.$right_width;
        $catHtml = '';
        $mainWrapperClass = 'halfmenu clearfix content-only';
        if ($isFullWidth == 1) {
            $mainWrapperClass = 'megamenu fullmenu clearfix linksmenu content-only';
        }
        $catHtml .= '<div class="'.$mainWrapperClass.'">';
        $catHtml .= '<div class="'.$this->_customhelper->menuContainerClass().'">';
            if ($main_cat->getMegamenuTypeHeader() != '') {
                $catHtml .= '<div class="menuheader root-col-1 clearfix">';
                    $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_header'));
                $catHtml .= '</div>';   
            }
            $catHtml .= '<div class="root-col-1 clearfix">';
                if ($left_width != 0) {
                    $catHtml .= '<div class="'.$left_content_area.' clearfix rootmegamenu_block">';
                        $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_leftblock'));
                    $catHtml .= '</div>';   
                }
                if ($right_width != 0) {
                    $catHtml .= '<div class="'.$right_content_area.' clearfix rootmegamenu_block">';
                        $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_rightblock'));
                    $catHtml .= '</div>';
                }
            $catHtml .= '</div>';
            if ( $main_cat->getMegamenuTypeFooter() != '' ) {
                $catHtml .= '<div class="menufooter root-col-1 clearfix">';
                    $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_footer'));
                $catHtml .= '</div>';   
            }  
        $catHtml .= '</div></div>';
		return $catHtml;
    }
    
    public function getLastCatHtml($mainCat, $childrenCategories_3, $navCnt0, $navCnt, $navCnt1)
    {
        $navCnt2 = 0;
        $catHtml = '';
        foreach ($childrenCategories_3 as $childCategory3) {
            $navCnt2++;
            $load_cat_sub_sub = $this->categoryRepository->get($childCategory3->getId(), $this->_customhelper->getStoreId());
            $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.'-'.$navCnt1.'-'.$navCnt2.' category-item"><a href='.$load_cat_sub_sub->getURL().'>';
            $catHtml .= $this->getImageHtml($mainCat, $load_cat_sub_sub, 3);
            $catHtml .= '<span class="level4-name">'.$load_cat_sub_sub->getName().'</span></a></li>';
        }
        return $catHtml; 
    }
    
    public function getImageHtml($mainCat, $currentCat, $catLevel)
    {
        if ($catLevel == 1) {
            $w = $mainCat->getMegamenuShowCatimageWidth().'px';
            $h = $mainCat->getMegamenuShowCatimageHeight().'px';
            $arrowHtml = '<span class="cat-arrow"></span>';
        } else {
            $w = '25px';
            $h = '25px';
            $arrowHtml = ''; // This is for sub-sub categoires layout. Arrow is not requried for sub-sub categories.
        }
        
        if ($mainCat->getMegamenuShowCatimage() == 1) {
            if ($this->_customhelper->getMegaMenuImageName($currentCat) != '') {
                $imageurl = $this->_customhelper->getMegaMenuImageUrl($currentCat);
            } else {
                $imageurl = $this->getViewFileUrl('Rootways_Megamenu::images/rootimgicon.jpg');
            }
            $image_html = '<img class="cat_img_as_icon" style="width:'.$w.'; height:'.$h.';" src='.$imageurl.' alt="'.$currentCat->getName().'"/>';
        } else {
            $image_html = $arrowHtml;
        }
        return $image_html;
    }
    
    public function getImageHtmlAsTitle($mainCat, $currentCat, $catLevel)
    {
        $image_html = '';
        if ($mainCat->getMegamenuShowCatimage() == 1 && $this->_customhelper->getMegaMenuImageName($currentCat) != '') {
            $imageurl = $this->_customhelper->getMegaMenuImageUrl($currentCat);
            $style = '';
            if ($mainCat->getMegamenuShowCatimageWidth() != '' && $mainCat->getMegamenuShowCatimageHeight() != '') {
                $style = 'style="width:'.$mainCat->getMegamenuShowCatimageWidth().'px; height:'.$mainCat->getMegamenuShowCatimageHeight().'px;"';    
            }
            $image_html = '<a href='.$currentCat->getURL().' class="catproductimg"><img  '.$style.' src='.$imageurl.' alt="'.$currentCat->getName().'"/></a>';
        }
        return $image_html;
    }
	
    /**
     * Half-Width With Title Mega Menu HTML Block.
     */
    public function halfTitleMenu($category, $navCnt0)
	{
        $main_cat = $this->categoryRepository->get($category->getId(), $this->_customhelper->getStoreId());
        
        $viewMoreAfter = $main_cat->getMegamenuTypeViewmore();
		$colnum = $main_cat->getMegamenuTypeNumofcolumns();
		if ($colnum == 0) {
            $colnum = 2;
        }
        $left_width = $main_cat->getMegamenuTypeLeftblockW();
        $right_width = $main_cat->getMegamenuTypeRightblockW();
        $cat_width = 12 - ($left_width + $right_width);
        $category_area_width = 'root-sub-col-'.$cat_width;
        $left_content_area = 'root-sub-col-'.$left_width;
        $right_content_area = 'root-sub-col-'.$right_width;
        $catHtml = '';
        if ($childrenCategories = $this->getChildCategories($category)) {
            $catHtml .= '<div class="halfmenu clearfix">';
                if ($main_cat->getMegamenuTypeHeader() != '') {
                    $catHtml .= '<div class="menuheader root-col-1 clearfix">';
                        $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_header'));
				    $catHtml .= '</div>';   
                }
                $catHtml .= '<div class="root-col-1 clearfix">';
                    if ($left_width != 0) {
                        $catHtml .= '<div class="'.$left_content_area.' clearfix rootmegamenu_block">';
                            $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_leftblock'));
                        $catHtml .= '</div>';   
                    }
                    $catHtml .= '<div class="'.$category_area_width.' grid clearfix">';
                        $cat_cnt = 1;
                        // 2nd Level Category
                        foreach ($childrenCategories as $childCategory) {
                            $catHtml .= '<div class="root-col-'.$colnum.' clearfix ">';
                                $load_cat = $this->categoryRepository->get($childCategory->getId(), $this->_customhelper->getStoreId());
                                $imageurl = $this->_customhelper->getMegaMenuImageUrl($load_cat);
                                $catHtml .= $this->getImageHtmlAsTitle($main_cat, $load_cat, 1);

                                $catHtml .= '<div class="title"><a href='.$load_cat->getURL().'>'.$load_cat->getName().'</a>';
                                if ($load_cat->getMegamenuTypeLabeltx() != '') {
                                    $catHtml .= '<span class="top-sub-label"><em class="rootmenutag" style="background-color: #'.$load_cat->getMegamenuTypeLabelclr().'">'.$load_cat->getMegamenuTypeLabeltx().'</em></span>';
                                }
                                $catHtml .= '</div>';
                                // 3th Level Category
                                if ($childrenCategories_2 = $this->getChildCategories($childCategory)) {
                                    $catHtml .= '<ul class="level3-listing">';
                                        $subCatCnt = 0;
                                        foreach ($childrenCategories_2 as $childCategory2) {
                                            $load_cat_sub = $this->categoryRepository->get($childCategory2->getId(), $this->_customhelper->getStoreId());
                                            if ($subCatCnt >= $viewMoreAfter && $viewMoreAfter != '') {
                                                $catHtml .= '<li><a class="view-more" href='.$load_cat->getURL().'>'.__('View More').'</a></li>';
                                                break;
                                            }
                                            $subCatCnt++;
                                            $image_html = $this->getImageHtml($main_cat, $load_cat_sub, 2);
                                            $catHtml .= '<li><a href='.$load_cat_sub->getURL().'>';
                                            $catHtml .= $image_html;
                                            $catHtml .= '<span class="level2-name sub-cat-name">'.$load_cat_sub->getName().'</span>';
                                            if ($load_cat_sub->getMegamenuTypeLabeltx() != '') {
                                                $catHtml .= '<span class="top-sub-label"><em class="rootmenutag" style="background-color: #'.$load_cat_sub->getMegamenuTypeLabelclr().'">'.$load_cat_sub->getMegamenuTypeLabeltx().'</em></span>';
                                            }

                                            $childrenCategories_3 = $load_cat_sub->getChildrenCategories();
                                            if (count($childrenCategories_3)) {
                                                $catHtml .= '<span class="cat-arrow"></span>';
                                            }
                                            $catHtml .= '</a>';
                                            if (count($childrenCategories_3))  {
                                                $catHtml .= '<span class="rootmenu-click"><i class="rootmenu-arrow"></i></span>';
                                                $catHtml .= '<ul class="level4-listing">';
                                                    $subSubCatCnt = 0;
                                                    foreach ($childrenCategories_3 as $childCategory3) {
                                                        if ($subSubCatCnt >= $viewMoreAfter && $viewMoreAfter != '') {
                                                            $catHtml .= '<li><a class="view-more" href='.$load_cat_sub->getURL().'><span class="level3-name">'.__('View More').'</span></a></li>';
                                                            break;
                                                        }
                                                        $subSubCatCnt++;

                                                        $catHtml .= '<li><a href='.$childCategory3->getURL().'>';
                                                        //$catHtml .= $image_html_sub;
                                                        $catHtml .= '<span class="level3-name">'.$childCategory3->getName().'</span>';
                                                        $catHtml .='</a></li>';
                                                    }
                                                $catHtml .= '</ul>';
                                            }
                                            $catHtml .= '</li>';
                                        }
                                    $catHtml .= '</ul>';
                                }
                            $catHtml .= '</div>';
                            /*
                            if ( $cat_cnt%$rightcol_type_num_of_col==0 ) {
                                $catHtml .= '<div class="clearfix"></div>';
                            }
                            */
                            $cat_cnt++;
                        }
                        $catHtml .= '</div>';

                    if ( $right_width != 0 ) {
                        $catHtml .= '<div class="'.$right_content_area.' clearfix rootmegamenu_block">';
                            $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_rightblock'));
                        $catHtml .= '</div>';
                    }
                $catHtml .= '</div>';
                
                if ( $main_cat->getMegamenuTypeFooter() != '' ) {
                    $catHtml .= '<div class="menufooter root-col-1 clearfix">';
                        $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_footer'));
                    $catHtml .= '</div>';   
                }        
			$catHtml .= '</div>';
		}
		return $catHtml;
	}
    
    /**
     * Half-Width Menu With Content Only HTML Block.
     */
    public function halfMenuContentOnly($category, $navCnt0)
	{
        return $this->halfFullContentOnlyHtml($category, 0);
	}
    
    /**
     * Half-Width with Image and Title Only Mega Menu HTML Block.
     */
    public function halfMenuImageOnly($category, $navCnt0)
	{
        $catHtml = $this->getHtmlImageOnly('half' ,$category, $navCnt0);
        return $catHtml;
	}
    
    /**
     * Full-Width Mega Menu HTML Block.
     */
	public function fullWidthMenu($category, $navCnt0)
	{
       return $this->halfFullHtml($category, $navCnt0, '1');
	}
    
    /**
     * Full-Width With Right Side Content Mega Menu HTML Block.
     */
	public function fullTitleMenu($category, $navCnt0)
	{
        $main_cat = $this->categoryRepository->get($category->getId(), $this->_customhelper->getStoreId());
        $viewMoreAfter = $main_cat->getMegamenuTypeViewmore();
		$colnum = $main_cat->getMegamenuTypeNumofcolumns();
		if ($colnum == 0) {
            $colnum = 2;
        }
        $subColnum = $main_cat->getMegamenuTypeSubcol();
        if ($subColnum == 0) {
            $subColnum = 1;
        }
        $left_width = $main_cat->getMegamenuTypeLeftblockW();
        $right_width = $main_cat->getMegamenuTypeRightblockW();
        $cat_width = 12 - ($left_width + $right_width);
        $category_area_width = 'root-sub-col-'.$cat_width;
        $left_content_area = 'root-sub-col-'.$left_width;
        $right_content_area = 'root-sub-col-'.$right_width;
        $msClasses = $this->masonryCategoryClass($category->getId());
        $masonryClass = $msClasses[0];
        $colClass = $msClasses[1];
        $catHtml = '';
        if ($childrenCategories = $this->getChildCategories($category)) {
			$catHtml .= '<div class="megamenu fullmenu clearfix categoriesmenu">';
            $catHtml .= '<div class="container '.$this->_customhelper->menuContainerClass().'">';
                if ($main_cat->getMegamenuTypeHeader() != '') {
                    $catHtml .= '<div class="menuheader root-col-1 clearfix">';
                        $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_header'));
				    $catHtml .= '</div>';   
                }
                $catHtml .= '<div class="root-col-1 clearfix">';
                    if ($left_width != 0) {
                        $catHtml .= '<div class="'.$left_content_area.' clearfix left rootmegamenu_block">';
                            $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_leftblock'));
                        $catHtml .= '</div>';
                        $catHtml .= '<div class="main_categoryblockcontent">'.$this->getBlockContent($main_cat->getData('megamenu_type_leftblock')).'</div>';
                    } else {
                        $catHtml .= '<div class="main_categoryblockcontent">'.$this->getBlockContent($main_cat->getData('megamenu_type_rightblock')).'</div>';
                    }
                    $catHtml .= '<div class="'.$category_area_width.' clearfix'.$masonryClass.'">';
                        $cat_cnt = 1;
                        // 2rd Level Category
                        foreach ($childrenCategories as $childCategory) {
                            $catHtml .= '<div class="'.$colClass.$colnum.' clearfix ">';
                                $load_cat = $this->categoryRepository->get($childCategory->getId(), $this->_customhelper->getStoreId());
                                $imageurl = $this->_customhelper->getMegaMenuImageUrl($load_cat);
                                $catHtml .= $this->getImageHtmlAsTitle($main_cat, $load_cat, 1);

                                $catHtml .= '<div class="title"><a href='.$load_cat->getURL().'>'.$load_cat->getName().'</a>';
                                if ($load_cat->getMegamenuTypeLabeltx() != '') {
                                    $catHtml .= '<span class="top-sub-label"><em class="rootmenutag" style="background-color: #'.$load_cat->getMegamenuTypeLabelclr().'">'.$load_cat->getMegamenuTypeLabeltx().'</em></span>';
                                }
                                $catHtml .= '</div>';
                                // 3th Level Category
                                if ($childrenCategories_2 = $this->getChildCategories($childCategory)) {
                                    $catHtml .= '<ul class="level3-listing root-col-'.$subColnum.' clearfix">';
                                        $subCatCnt = 0;
                                        $cat_tot = count($childrenCategories_2);
                                        $brk = ceil($cat_tot/$subColnum);
                                        foreach ($childrenCategories_2 as $childCategory2) {
                                            $load_cat_sub = $this->categoryRepository->get($childCategory2->getId(), $this->_customhelper->getStoreId());
                                            if ($subCatCnt >= $viewMoreAfter && $viewMoreAfter != '') {
                                                $catHtml .= '<li><a class="view-more" href='.$load_cat->getURL().'>'.__('View More').'</a></li>';
                                                break;
                                            }
                                            $subCatCnt++;
                                            $image_html = $this->getImageHtml($main_cat, $load_cat_sub, 2);
                                            $catHtml .= '<li><a href='.$load_cat_sub->getURL().'>';
                                            $catHtml .= $image_html;
                                            $catHtml .= '<span class="level2-name sub-cat-name">'.$load_cat_sub->getName().'</span>';
                                            if ($load_cat_sub->getMegamenuTypeLabeltx() != '') {
                                                $catHtml .= '<span class="top-sub-label"><em class="rootmenutag" style="background-color: #'.$load_cat_sub->getMegamenuTypeLabelclr().'">'.$load_cat_sub->getMegamenuTypeLabeltx().'</em></span>';
                                            }

                                            $childrenCategories_3 = $load_cat_sub->getChildrenCategories();
                                            if (count($childrenCategories_3)) {
                                                $catHtml .= '<span class="cat-arrow"></span>';
                                            }
                                            $catHtml .= '</a>';
                                            
                                            
                                            /*
                                            $catHtml .= '<li><a href='.$load_cat_sub->getURL().'>'.$load_cat_sub->getName();
                                                if ($load_cat_sub->getMegamenuTypeLabeltx() != '') {
                                                    $catHtml .= '<span class="top-sub-label"><em class="rootmenutag" style="background-color: #'.$load_cat_sub->getMegamenuTypeLabelclr().'">'.$load_cat_sub->getMegamenuTypeLabeltx().'</em></span>';
                                                }
                                                $childrenCategories_3 = $load_cat_sub->getChildrenCategories();
                                                if (count($childrenCategories_3)) {
                                                    $catHtml .= '<span class="cat-arrow"></span>';
                                                }
                                                $catHtml .= '</a>';
                                            */
                                                if ($left_width != 0 && $load_cat_sub->getData('megamenu_type_leftblock') != '') {
                                                     $catHtml .= '<div class="categoryblockcontent">'.$this->getBlockContent($load_cat_sub->getData('megamenu_type_leftblock')).'</div>'; 
                                                } else if ($right_width != 0 && $load_cat_sub->getData('megamenu_type_rightblock') != '') {
                                                    $catHtml .= '<div class="categoryblockcontent">'.$this->getBlockContent($load_cat_sub->getData('megamenu_type_rightblock')).'</div>';
                                                } if ($left_width != 0 && $load_cat->getData('megamenu_type_leftblock') != '') {
                                                     $catHtml .= '<div class="categoryblockcontent">'.$this->getBlockContent($load_cat->getData('megamenu_type_leftblock')).'</div>'; 
                                                } else if ($right_width != 0 && $load_cat->getData('megamenu_type_rightblock') != '') {
                                                    $catHtml .= '<div class="categoryblockcontent">'.$this->getBlockContent($load_cat->getData('megamenu_type_rightblock')).'</div>';
                                                } else { }

                                            if (count($childrenCategories_3)) {
                                                $catHtml .= '<span class="rootmenu-click"><i class="rootmenu-arrow"></i></span>';
                                                $catHtml .= '<ul class="level4-listing">';
                                                    $subSubCatCnt = 0;
                                                    foreach ($childrenCategories_3 as $childCategory3) {
                                                        if ($subSubCatCnt >= $viewMoreAfter && $viewMoreAfter != '') {
                                                            $catHtml .= '<li><a class="view-more" href='.$load_cat_sub->getURL().'><span class="level3-name">'.__('View More').'</span></a></li>';
                                                            break;
                                                        }
                                                        $subSubCatCnt++;
                                                        
                                                        $catHtml .= '<li><a href='.$childCategory3->getURL().'>';
                                                        //$catHtml .= $image_html_sub;
                                                        $catHtml .= '<span class="level3-name">'.$childCategory3->getName().'</span>';
                                                        $catHtml .='</a></li>';
                                                    }
                                                $catHtml .= '</ul>';
                                            }
                                            $catHtml .= '</li>';
                                            if ($subCatCnt%$brk == 0 && $subCatCnt != $cat_tot) {$catHtml .= '</ul><ul class="level3-listing root-col-'.$subColnum.' clearfix">';}
                                        }
                                    $catHtml .= '</ul>';
                                }
                            $catHtml .= '</div>';
                            if ($masonryClass != ' grid' && $cat_cnt%$colnum==0) {
                                $catHtml .= '<div class="clearfix"></div>';
                            }
                            $cat_cnt++;
                        }
                    $catHtml .= '</div>';
                    if ( $right_width != 0 ) {
                        $catHtml .= '<div class="'.$right_content_area.' clearfix right rootmegamenu_block">';
                            $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_rightblock'));
                        $catHtml .= '</div>';
                    }
                $catHtml .= '</div>';
                if ( $main_cat->getMegamenuTypeFooter() != '' ) {
                    $catHtml .= '<div class="menufooter root-col-1 clearfix">';
                        $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_footer'));
                    $catHtml .= '</div>';   
                }
			$catHtml .= '</div></div>';
		}
		return $catHtml;
	}
    
    /**
     * Full-Width Content Only Mega Menu HTML Block.
     */
	public function fullWidthContentOnly($category, $navCnt0)
	{
        return $this->halfFullContentOnlyHtml($category, 1);
	}
    
    /**
     * Full-Width with Image and Title Olny Mega Menu HTML Block.
     */
	public function fullWidthImageOnly($category, $navCnt0)
	{
        $catHtml = $this->getHtmlImageOnly('full' ,$category, $navCnt0);
        return $catHtml;
	}
    
    public function getHtmlImageOnly($mType, $category, $navCnt0)
	{
        $main_cat = $this->categoryRepository->get($category->getId(), $this->_customhelper->getStoreId());
		$colnum = $main_cat->getMegamenuTypeNumofcolumns();
		if ($colnum == 0) {
            $colnum = 5;
            if ($mType == 'half') {
                $colnum = 2;
            }
        }
        $left_width = $main_cat->getMegamenuTypeLeftblockW();
        $right_width = $main_cat->getMegamenuTypeRightblockW();
        $cat_width = 12 - ($left_width + $right_width);
        $category_area_width = 'root-sub-col-'.$cat_width;
        $left_content_area = 'root-sub-col-'.$left_width;
        $right_content_area = 'root-sub-col-'.$right_width;
        $catHtml = '';
        $mainClass = 'megamenu fullmenu clearfix categoriesmenu rwimageonly';
        if ($mType == 'half') {
            $mainClass = 'halfmenu clearfix rwimageonly';
        }
        if ($childrenCategories = $this->getChildCategories($category)) {
			$catHtml .= '<div class="'.$mainClass.'">';
            $catHtml .= '<div class="container '.$this->_customhelper->menuContainerClass().'">';
                if ($main_cat->getMegamenuTypeHeader() != '') {
                    $catHtml .= '<div class="menuheader root-col-1 clearfix">';
                        $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_header'));
				    $catHtml .= '</div>';   
                }
                $catHtml .= '<div class="root-col-1 clearfix">';
                    if ($left_width != 0) {
                        $catHtml .= '<div class="'.$left_content_area.' clearfix left rootmegamenu_block">';
                            $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_leftblock'));
                        $catHtml .= '</div>';
                        $catHtml .= '<div class="main_categoryblockcontent">'.$this->getBlockContent($main_cat->getData('megamenu_type_leftblock')).'</div>';
                    } else {
                        $catHtml .= '<div class="main_categoryblockcontent">'.$this->getBlockContent($main_cat->getData('megamenu_type_rightblock')).'</div>';

                    }
                    $catHtml .= '<div class="'.$category_area_width.' clearfix">';
                        $cat_cnt = 1;
                        // 2rd Level Category

                        foreach ($childrenCategories as $childCategory) {
                            $catHtml .= '<div class="root-col-'.$colnum.' clearfix ">';
                                $load_cat = $this->categoryRepository->get($childCategory->getId(), $this->_customhelper->getStoreId());
                                $imgHtml = $this->getImageHtmlAsTitle($main_cat, $load_cat, 1);
                                if ($main_cat->getMegamenuTypeImgpos() == '0') {
                                    $catHtml .= $imgHtml;
                                }
                                if ($main_cat->getMegamenuTypeShowtitle() == '1') {
                                    $catHtml .= '<div class="title"><a href='.$load_cat->getURL().'>'.$load_cat->getName().'</a>';
                                    if ($load_cat->getMegamenuTypeLabeltx() != '') {
                                        $catHtml .= '<span class="top-sub-label"><em class="rootmenutag" style="background-color: #'.$load_cat->getMegamenuTypeLabelclr().'">'.$load_cat->getMegamenuTypeLabeltx().'</em></span>';
                                    }
                                    $catHtml .= '</div>';
                                }
                                if ($main_cat->getMegamenuTypeImgpos() == '1') {
                                    $catHtml .= $imgHtml;
                                }
                            $catHtml .= '</div>';
                            if ($cat_cnt%$colnum==0) {
                                $catHtml .= '<div class="clearfix"></div>';
                            }

                            $cat_cnt++;
                        }
                    $catHtml .= '</div>';


                    if ( $right_width != 0 ) {
                        $catHtml .= '<div class="'.$right_content_area.' clearfix right rootmegamenu_block">';
                            $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_rightblock'));
                        $catHtml .= '</div>';
                    }
                $catHtml .= '</div>';

                if ( $main_cat->getMegamenuTypeFooter() != '' ) {
                    $catHtml .= '<div class="menufooter root-col-1 clearfix">';
                        $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_footer'));
                    $catHtml .= '</div>';   
                }
			$catHtml .= '</div></div>';
		}
		return $catHtml;
    }
	
    /**
     * Tabbing Mega Menu HTML Block.
     */  	
	public function tabMenu($category, $navCnt0)
	{
        $main_cat = $this->categoryRepository->get($category->getId(), $this->_customhelper->getStoreId());
        
        $viewMoreAfter = $main_cat->getMegamenuTypeViewmore();
        $colnum = $main_cat->getMegamenuTypeNumofcolumns();
		if ($colnum == 0) {
            $colnum = 5;
        }
        $left_width = $main_cat->getMegamenuTypeLeftblockW();
        $right_width = $main_cat->getMegamenuTypeRightblockW();
        $cat_width = 12 - ($left_width + $right_width);
        $category_area_width = 'root-sub-col-'.$cat_width;
        $left_content_area = 'root-sub-col-'.$left_width;
        $right_content_area = 'root-sub-col-'.$right_width;
        $msClasses = $this->masonryCategoryClass($category->getId());
        $masonryClass = $msClasses[0];
        $colClass = $msClasses[1];
		$catHtml = '';
		if ($childrenCategories = $this->getChildCategories($category)) {
			$catHtml .= '<div class="megamenu fullmenu clearfix tabmenu">';
            $catHtml .= '<div class="'.$this->_customhelper->menuContainerClass().'">';
			$catHtml .= '<div class="mainmenuwrap clearfix">';
			$catHtml .= '<ul class="root-col-1 clearfix vertical-menu">';
				$cnt = 0;
                 // 2nd Level Category
				foreach ($childrenCategories as $childCategory) {
                    $load_cat = $this->categoryRepository->get($childCategory->getId(), $this->_customhelper->getStoreId());
                    $left_sub_width = $load_cat->getMegamenuTypeLeftblockW();
                    $right_sub_width = $load_cat->getMegamenuTypeRightblockW();
                    $cat_sub_width = 12 - ($left_sub_width + $right_sub_width);
                    $sub_category_area_width = 'root-sub-col-'.$cat_sub_width;
                    $sub_left_content_area = 'root-sub-col-'.$left_sub_width;
                    $sub_right_content_area = 'root-sub-col-'.$right_sub_width;
                    if ($left_sub_width != 0 || $right_sub_width != 0) {
                        $category_area_width = 'root-sub-col-'.$cat_sub_width;
                    } else {
                        $category_area_width = 'root-sub-col-'.$cat_width;
                    }
                    
                    if ($cnt == 0) {
                        $open = "main_openactive01";
                    } else {
                        $open = "";
                    } $cnt++;
					$catHtml .= '<li class="clearfix '.$open.'"><a href='.$load_cat->getUrl().' class="root-col-4">';
                    $catHtml .= $this->getImageHtml($main_cat, $load_cat, 2);
                    $catHtml .= $load_cat->getName().'<span class="cat-arrow"></span></a>';
                    if ($childrenCategories_2 = $this->getChildCategories($childCategory)) {
						$catHtml .= '<div class="root-col-75 verticalopen">';
                            if ($load_cat->getMegamenuTypeHeader() != '') {
                                $catHtml .= '<div class="menuheader root-col-1 clearfix">';
                                    $catHtml .= $this->getBlockContent($load_cat->getData('megamenu_type_header'));
                                $catHtml .= '</div>';   
                            } elseif ($main_cat->getMegamenuTypeHeader() != '') {
                                $catHtml .= '<div class="menuheader root-col-1 clearfix">';
                                    $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_header'));
                                $catHtml .= '</div>';   
                            }
                            $catHtml .= '<div class="padding-zero root-col-1 clearfix">';
                                if ($left_width != 0 || $left_sub_width != 0) {
                                    $left_sub_content = $this->getBlockContent($load_cat->getData('megamenu_type_leftblock'));
                                    if ($left_sub_content != '') {
                                        $catHtml .= '<div class="'.$sub_left_content_area.' clearfix rootmegamenu_block">';
                                            $catHtml .= $left_sub_content;
                                        $catHtml .= '</div>';   
                                    } else {
                                        $catHtml .= '<div class="'.$left_content_area.' clearfix rootmegamenu_block">';
                                            $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_leftblock'));
                                        $catHtml .= '</div>';
                                    }
                                }
                                $catHtml .= '<div class="'.$category_area_width.' clearfix padding-zero'.$masonryClass.'">';
                                    $sub_cnt = 1;
                                    // 3th Level Category
                                    foreach ($childrenCategories_2 as $childCategory2) {
                                        if ($main_cat->getMegamenuShowCatimage() == 1) {
                                            $brake_point = $colnum * 2;
                                        } else {
                                            $brake_point = $colnum * 6;	
                                        }
                                        //if ($sub_cnt > $brake_point) { continue; }
                                        $sub_cnt++;
                                        $load_cat_sub = $this->categoryRepository->get($childCategory2->getId(), $this->_customhelper->getStoreId());
                                        
                                        $catHtml .= '<div class="tabimgwpr '.$colClass.$colnum.' clearfix ">';
                                        //$catHtml .= '<div class="tabimgwpr root-col-'.$colnum.'">;
                                        $catHtml .= '<a href='.$load_cat_sub->getURL().' class="tabimtag">';
                                        if ($main_cat->getMegamenuShowCatimage() == 1) {
                                            if ($this->_customhelper->getMegaMenuImageName($load_cat_sub) != '') {
                                                $imageurl = $this->_customhelper->getMegaMenuImageUrl($load_cat_sub);
                                            } else { 
                                                $imageurl = $this->getViewFileUrl('Rootways_Megamenu::images/rootimgicon.jpg');
                                            }
                                            $catHtml .= '<img width='.$main_cat->getMegamenuShowCatimageWidth().' height='.$main_cat->getMegamenuShowCatimageHeight().' src='.$imageurl.' alt="'.$main_cat->getName().'"/>';
                                        }
                                        $catHtml .= '<div class="tabimgtext">'.$load_cat_sub->getName().'</div></a>';
                                        
                                        if ($childrenCategories_3 = $this->getChildCategories($childCategory2)) {
                                            $catHtml .= '<ul class="tabbing_lev4">';
                                            $subCatCnt = 0;
                                            foreach ($childrenCategories_3 as $childCategory3) {
                                                if ($subCatCnt >= $viewMoreAfter && $viewMoreAfter != '') {
                                                    $catHtml .= '<li><a class="view-more" href='.$load_cat_sub->getURL().'>'.__('View More').'</a></li>';
                                                    break;
                                                }
                                                $subCatCnt++;
                                                $load_cat_sub_sub = $this->categoryRepository->get($childCategory3->getId(), $this->_customhelper->getStoreId());
                                                $image_html_sub_sub = $this->getImageHtml($main_cat, $load_cat_sub_sub, 3);
                                                $catHtml .= '<li><a href='.$load_cat_sub_sub->getURL().'>';
                                                $catHtml .= $image_html_sub_sub;
                                                $catHtml .= '<span class="level4-name">'.$load_cat_sub_sub->getName().'</span></a></li>';
                                            }
                                            $catHtml .= '</ul>';
                                        }
                                        $catHtml .= '</div>';
                                        if ($masonryClass != ' grid' && $sub_cnt%$colnum==0) {
                                            $catHtml .= '<div class="clearfix"></div>';
                                        }
                                    }
                                    /*
                                    if ($sub_cnt > $brake_point) {
                                        $catHtml .= '<a href='.$load_cat->getURL().' class="view_all">View All &raquo;</a>';
                                    }*/
                                $catHtml .= '</div>';
                                if ($right_width != 0 || $right_sub_width != 0) {
                                    $right_sub_content = $this->getBlockContent($load_cat->getData('megamenu_type_rightblock'));
                                    if ($right_sub_content != '') {
                                        $catHtml .= '<div class="'.$sub_right_content_area.' clearfix rootmegamenu_block">';
                                            $catHtml .= $right_sub_content;
                                        $catHtml .= '</div>';   
                                    } else {
                                        $catHtml .= '<div class="'.$right_content_area.' clearfix rootmegamenu_block">';
                                            $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_rightblock'));
                                        $catHtml .= '</div>';
                                    }
                                }
                            $catHtml .= '</div>';
                            if ( $load_cat->getMegamenuTypeFooter() != '' ) {
                                $catHtml .= '<div class="menufooter root-col-1 clearfix">';
                                    $catHtml .= $this->getBlockContent($load_cat->getData('megamenu_type_footer'));
                                $catHtml .= '</div>';   
                            } elseif( $main_cat->getMegamenuTypeFooter() != '' ) {
                                $catHtml .= '<div class="menufooter root-col-1 clearfix">';
                                    $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_footer'));
                                $catHtml .= '</div>';
                            } else { }
						$catHtml .= '</div>';
					 } else {
						$catHtml .= '<div class="root-col-75 verticalopen empty_category">';
						    $catHtml .= '<span>Sub-category not found for '.$load_cat->getName().' Category</span>';
						$catHtml .= '</div>';
                    }
					$catHtml .= '</li>';
				}
			$catHtml .= '</ul>';
			$catHtml .= '</div>';
			$catHtml .= '</div></div>';
		}
		return $catHtml;	
	}
    
    /**
     * Full-Width Horizontal Mega Menu HTML Block.
     */
    public function tabHorizontal($category, $navCnt0)
    {
        $main_cat = $this->categoryRepository->get($category->getId(), $this->_customhelper->getStoreId());
        $left_width = $main_cat->getMegamenuTypeLeftblockW();
        $right_width = $main_cat->getMegamenuTypeRightblockW();
        $cat_width = 12 - ($left_width + $right_width);
        $category_area_width = 'root-sub-col-'.$cat_width;
        $left_content_area = 'root-sub-col-'.$left_width;
        $right_content_area = 'root-sub-col-'.$right_width;
        $catHtml = '';
        if ($childrenCategories = $this->getChildCategories($category)) {
            $catHtml .= '<div class="megamenu fullmenu clearfix tabmenu02">';
                $catHtml.= '<div class="mainmenuwrap02 clearfix">';
                    $catHtml .= '<ul class="vertical-menu02 root-col-1 clearfix">';
                        foreach ($childrenCategories as $childCategory) {
                            $load_cat = $this->categoryRepository->get($childCategory->getId(), $this->_customhelper->getStoreId());
                            $left_sub_width = $load_cat->getMegamenuTypeLeftblockW();
                            $right_sub_width = $load_cat->getMegamenuTypeRightblockW();
                            $cat_sub_width = 12 - ($left_sub_width + $right_sub_width);
                            $sub_category_area_width = 'root-sub-col-'.$cat_sub_width;
                            $sub_left_content_area = 'root-sub-col-'.$left_sub_width;
                            $sub_right_content_area = 'root-sub-col-'.$right_sub_width;
                            if ($left_sub_width != 0 || $right_sub_width != 0) {
                                $category_area_width = 'root-sub-col-'.$cat_sub_width;
                            } else {
                                $category_area_width = 'root-sub-col-'.$cat_width;
                            }
                            $catHtml .= '<li class="clearfix"><a class="clearfix" style="line-height:'.$main_cat->getMegamenuShowCatimageHeight().'px;" href='.$load_cat->getURL().'>';
                            
                            /*
							if ($main_cat->getMegamenuShowCatimage() == 1) {
								if ($this->_customhelper->getMegaMenuImageName($load_cat) != '') {
									$imageurl = $this->_customhelper->getMegaMenuImageUrl($load_cat);
								} else {
									$imageurl = $this->getViewFileUrl('Rootways_Megamenu::images/rootimgicon.jpg');
								}
								$catHtml .= ' <span><img style="width:'.$main_cat->getMegamenuShowCatimageWidth().'px; height:'.$main_cat->getMegamenuShowCatimageHeight().'px;" src='.$imageurl.' alt="'.$load_cat->getName().'"/></span>';	
							}
                            */
							$catHtml .= '<em>'.$load_cat->getName().'</em></a>';
                            if ($childrenCategories_2 = $this->getChildCategories($childCategory)) {
                                $num_of_col = $load_cat->getMegamenuTypeNumofcolumns();
                                if ($num_of_col == 0) {
                                    $num_of_col = 3;
                                }
                                $cnt = 0;
                                $cat_tot = count($childrenCategories_2);
                                $brk = ceil($cat_tot/$num_of_col);
                                
                                if ($cnt == 0) { $open = "openactive02"; } else { $open = ""; }
                                $cnt++;
                                $catHtml .= '<div class="root-col-1 verticalopen02 '.$open.'">';
                                $catHtml .= '<div class="'.$this->_customhelper->menuContainerClass().'">';
                                    if ($left_width != 0 || $left_sub_width != 0) {
                                        $left_sub_content = $this->getBlockContent($load_cat->getData('megamenu_type_leftblock'));
                                        if ($left_sub_content != '') {
                                            $catHtml .= '<div class="'.$sub_left_content_area.' clearfix rootmegamenu_block">';
                                                $catHtml .= $left_sub_content;
                                            $catHtml .= '</div>';   
                                        } else {
                                            $catHtml .= '<div class="'.$left_content_area.' clearfix rootmegamenu_block">';
                                                $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_leftblock'));
                                            $catHtml .= '</div>';
                                        }
                                    }
                                    $sub_cnt = 1;
                                    $catHtml .= '<div class="'.$category_area_width.' topmenu02-categories clearfix">';
                                        $catHtml .= '<div class="title"><a href="'.$load_cat->getURL().'">'.$load_cat->getName().'</a></div>';
                                        $catHtml .= '<div class="root-col-'.$num_of_col.' clearfix">';
                                            $catHtml .= '<ul class="ulliststy02">';
                                                $sub_cnt = 1;
                                                foreach ($childrenCategories_2 as $childCategory2) {
                                                    $load_sub_sub_cat = $this->categoryRepository->get($childCategory2->getId(), $this->_customhelper->getStoreId());
                                                    $catHtml .= '<li><a href='.$load_sub_sub_cat->getURL().'>';
                                                    $catHtml .= $this->getImageHtml($main_cat, $load_sub_sub_cat, 1);
                                                    $catHtml .= '<span class="level2-name sub-cat-name" style="height:'.$main_cat->getMegamenuShowCatimageHeight().'px;">'.$load_sub_sub_cat->getName();

                                                    if ($sub_cnt%$brk == 0) {
                                                        $catHtml .= '</ul></div> <div class="root-col-'.$num_of_col.' clearfix"><ul class="ulliststy02">';
                                                    }
                                                    $sub_cnt++;
                                                }
                                            $catHtml .= '</ul>';
                                        $catHtml .= '</div>';
                                    $catHtml .= '</div>';
                                    if ($right_width != 0 || $right_sub_width != 0) {
                                        $right_sub_content = $this->getBlockContent($load_cat->getData('megamenu_type_rightblock'));
                                        if ($right_sub_content != '') {
                                            $catHtml .= '<div class="'.$sub_right_content_area.' clearfix rootmegamenu_block">';
                                                $catHtml .= $right_sub_content;
                                            $catHtml .= '</div>';   
                                        } else {
                                            $catHtml .= '<div class="'.$right_content_area.' clearfix rootmegamenu_block">';
                                                $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_rightblock'));
                                            $catHtml .= '</div>';
                                        }
                                    }
                                $catHtml .= '</div></div>';
                             } else {
                                $catHtml .= '<div class="root-col-1 verticalopen02">';
                                    $catHtml .= '<span>There is no sub-category for '.$load_cat->getName().' category</span>';
                                $catHtml .= '</div>';
                             }
                            $catHtml .= '</li>';
                        }
                    $catHtml .= '</ul>';
                $catHtml .= '</div>';
            $catHtml .= '</div>';
        }
        return $catHtml;
    }
    
    /**
     * Multi Tabbing Mega Menu HTML Block.
     */
    public function multiTabbing($category, $navCnt0)
    {
        $main_cat = $this->categoryRepository->get($category->getId(), $this->_customhelper->getStoreId());
        $colnum = (int)$main_cat->getMegamenuTypeSubcatlevel();
        $viewMoreAfter = $main_cat->getMegamenuTypeViewmore();
		if ($colnum == '0') {
            $colnum = 4;
        }
        $catHtml = '';
        if ($childrenCategories = $this->getChildCategories($category)) {
            $catHtml .= '<div class="megamenu fullmenu clearfix fourcoltab multitabcol_'.$colnum.'">';
            $catHtml .= '<div class="'.$this->_customhelper->menuContainerClass().'">';
                $catHtml.= '<div class="mainmenuwrap clearfix">';
                    $catHtml .= '<ul class="colultabone">';
                        $navCnt = 0;
                        foreach ($childrenCategories as $childCategory) {
                            $navCnt++;
                            $openFirstLayer = '';
                            if ($navCnt == 1) { $openFirstLayer ='main_openactive03';}
                            $load_cat = $this->categoryRepository->get($childCategory->getId(), $this->_customhelper->getStoreId());
                            $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.' category-item clearfix '.$openFirstLayer.'"><a class="clearfix" style="line-height:'.$main_cat->getMegamenuShowCatimageHeight().'px;" href='.$load_cat->getURL().'>';
                                $catHtml .= $this->getImageHtml($main_cat, $load_cat, 2);
                                $catHtml .= '<em>'.$load_cat->getName().'</em></a>';
                                if ($colnum >= 2) {
                                    if ($childrenCategories_2 = $this->getChildCategories($childCategory)) {
                                        $catHtml .= '<span class="rootmenu-click"><i class="rootmenu-arrow"></i></span>';
                                    }
                                    if ( $childrenCategories_2 = $this->getChildCategories($childCategory) ) {
                                        $catHtml .= '<ul class="colultabtwo">';
                                            $navCnt1 = 0;
                                            foreach ($childrenCategories_2 as $childCategory2) {
                                                $navCnt1++;
                                                $openFirstLayer2 = '';
                                                if ($navCnt == 1 && $navCnt1 == 1) { $openFirstLayer2 ='main_openactive03_sub1';}
                                                $load_cat_sub = $this->categoryRepository->get($childCategory2->getId(), $this->_customhelper->getStoreId());
                                                $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.'-'.$navCnt1.' category-item clearfix '.$openFirstLayer2.'"><a class="clearfix" style="line-height:'.$main_cat->getMegamenuShowCatimageHeight().'px;" href='.$load_cat_sub->getURL().'>';
                                                    $catHtml .= $this->getImageHtml($main_cat, $load_cat_sub, 2);
                                                    $catHtml .= '<em>'.$load_cat_sub->getName().'</em></a>';
                                                    if ($colnum >= 3) {
                                                        if ($childrenCategories_3 = $this->getChildCategories($childCategory2)) {
                                                            $catHtml .= '<span class="rootmenu-click"><i class="rootmenu-arrow"></i></span>';
                                                        }
                                                        if ($childrenCategories_3 = $this->getChildCategories($childCategory2)) {

                                                            $catHtml .= '<ul class="colultabthree">';
                                                                $navCnt2 = 0;
                                                                foreach ($childrenCategories_3 as $childCategory3) {
                                                                    $navCnt2++;
                                                                    $openFirstLayer3 = '';
                                                                    if ($navCnt == 1 && $navCnt1 == 1 && $navCnt2 == 1) { $openFirstLayer3 ='main_openactive03_sub2';}
                                                                    $load_cat_sub_2 = $this->categoryRepository->get($childCategory3->getId(), $this->_customhelper->getStoreId());
                                                                    $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.'-'.$navCnt1.'-'.$navCnt2.' category-item clearfix '.$openFirstLayer3.'"><a class="clearfix" style="line-height:'.$main_cat->getMegamenuShowCatimageHeight().'px;" href='.$load_cat_sub_2->getURL().'>';
                                                                    $catHtml .= $this->getImageHtml($main_cat, $load_cat_sub_2, 2);
                                                                    $catHtml .= '<em>'.$load_cat_sub_2->getName().'</em></a>';
                                                                    if ($colnum >= 4) {
                                                                        if ($childrenCategories_4 = $this->getChildCategories($childCategory3)) {
                                                                            $catHtml .= '<span class="rootmenu-click"><i class="rootmenu-arrow"></i></span>';
                                                                        }
                                                                        if ($childrenCategories_4 = $this->getChildCategories($childCategory3)) {
                                                                            $catHtml .= '<div class="resultdiv clearfix">';
                                                                                $subCatCnt = 0;
                                                                                foreach ($childrenCategories_4 as $childCategory4) {
                                                                                    $load_cat_sub_3 = $this->categoryRepository->get($childCategory4->getId(), $this->_customhelper->getStoreId());
                                                                                    if ($subCatCnt >= $viewMoreAfter && $viewMoreAfter != '') {
                                                                                        $catHtml .= '<div class="root-col-1"><a class="view-more" href='.$load_cat_sub_2->getURL().'>'.__('View More').'</a></div>';
                                                                                        break;
                                                                                    }
                                                                                    $subCatCnt++;
                                                                                    $catHtml .= '<div class="root-col-3">';
                                                                                         if ($main_cat->getMegamenuShowCatimage() == 1) {
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
            $catHtml .= '</div></div>';
        }
		return $catHtml;
    }
	
    /**
     * Product Listing Mega Menu HTML Block.
     */   
	public function productMenu($category, $navCnt0)
	{
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$categoryHelper = $this->getCategoryHelper();
        $main_cat = $_objectManager->create('Magento\Catalog\Model\Category')->load($category->getId());
        $products = $main_cat->getProductCollection();
        $products->addAttributeToSelect('*');
        $catHtml = '';
        
        $media_url = $_objectManager->get('Magento\Store\Model\StoreManagerInterface')
                    ->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);        
        $currencysymbol = $_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currency = $currencysymbol->getStore()->getCurrentCurrencyCode();
        
        $catHtml .= '<div class="megamenu clearfix product-thumbnail">';
        $catHtml .= '<div class="'.$this->_customhelper->menuContainerClass().'">';
			$pro_cnt = 0;
       		foreach ( $products as $product ) {
				if ( $pro_cnt > 10 ) { 
                    continue;
                }
                $pro_cnt++;
                $productRepository = $_objectManager->get('\Magento\Catalog\Model\ProductRepository');
                $_product = $productRepository->getById($product->getId()); 
                
                $catHtml .= '<div class="root-col-5 clearfix">';
					$catHtml .= '<div class="probox01imgwp">';
						$catHtml .= '<div class="proimg"><a href='.$_product->getProductUrl().'><img src='.$media_url.'catalog/product'.$_product->getImage().' alt="'.$main_cat->getName().'"></a></div>';
					$catHtml .= '</div>';
				  	$catHtml .= '<div class="proinfo clearfix">';
						$catHtml .= '<div class="proname clearfix"><a href="#">'.$_product->getName().'</a></div>';
						$catHtml .= '<div class="pricebox"> <span>'.$currency.number_format($_product['price'],2).'</span> <a href="'.$_product->getProductUrl().'" class="addtocart-but">Add to Cart</a> </div>';
					  $catHtml .= '</div>';
				$catHtml .= '</div> ';
			}
        $catHtml .= '</div></div>';
		return $catHtml;
    }
    
    /**
     * Catepgur Product Mega Menu HTML Block.
     */  	
	public function categoryProductMenu($category, $navCnt0)
	{
        $main_cat = $this->categoryRepository->get($category->getId(), $this->_customhelper->getStoreId());
        $currency = $this->_customhelper->getCurrentStore()->getCurrentCurrencyCode();
        $media_url = $this->_customhelper->getCurrentStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $colnum = $main_cat->getMegamenuTypeNumofcolumns();
		if ($colnum == 0) {
            $colnum = 5;
        }
        $left_width = $main_cat->getMegamenuTypeLeftblockW();
        $right_width = $main_cat->getMegamenuTypeRightblockW();
        $cat_width = 12 - ($left_width + $right_width);
        $category_area_width = 'root-sub-col-'.$cat_width;
        $left_content_area = 'root-sub-col-'.$left_width;
        $right_content_area = 'root-sub-col-'.$right_width;
        $msClasses = $this->masonryCategoryClass($category->getId());
        $masonryClass = $msClasses[0];
        $colClass = $msClasses[1];
		$catHtml = '';
		if ($childrenCategories = $this->getChildCategories($category)) {
			$catHtml .= '<div class="megamenu fullmenu clearfix tabmenu categorywithproductmenu">';
            $catHtml .= '<div class="'.$this->_customhelper->menuContainerClass().'">';
			$catHtml .= '<div class="mainmenuwrap clearfix">';
			$catHtml .= '<ul class="root-col-1 clearfix vertical-menu">';
				$cnt = 0;
                 // 2nd Level Category
				foreach ($childrenCategories as $childCategory) {
                    $load_cat = $this->categoryRepository->get($childCategory->getId(), $this->_customhelper->getStoreId());
                    $left_sub_width = $load_cat->getMegamenuTypeLeftblockW();
                    $right_sub_width = $load_cat->getMegamenuTypeRightblockW();
                    $cat_sub_width = 12 - ($left_sub_width + $right_sub_width);
                    $sub_category_area_width = 'root-sub-col-'.$cat_sub_width;
                    $sub_left_content_area = 'root-sub-col-'.$left_sub_width;
                    $sub_right_content_area = 'root-sub-col-'.$right_sub_width;
                    if ($left_sub_width != 0 || $right_sub_width != 0) {
                        $category_area_width = 'root-sub-col-'.$cat_sub_width;
                    } else {
                        $category_area_width = 'root-sub-col-'.$cat_width;
                    }
                    
                    if ($cnt == 0) {
                        $open = "main_openactive01";
                    } else {
                        $open = "";
                    } $cnt++;
					$catHtml .= '<li class="clearfix '.$open.'"><a href='.$load_cat->getUrl().' class="root-col-4">'.$load_cat->getName().'<span class="cat-arrow"></span></a>';
                    $products = $load_cat->getProductCollection();
                    $products->addAttributeToSelect('*');
                    if (count($products) > 0) {
						$catHtml .= '<div class="root-col-75 padding-zero verticalopen">';
                            if ($load_cat->getMegamenuTypeHeader() != '') {
                                $catHtml .= '<div class="menuheader root-col-1 clearfix">';
                                    $catHtml .= $this->getBlockContent($load_cat->getData('megamenu_type_header'));
                                $catHtml .= '</div>';   
                            } elseif ($main_cat->getMegamenuTypeHeader() != '') {
                                $catHtml .= '<div class="menuheader root-col-1 clearfix">';
                                    $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_header'));
                                $catHtml .= '</div>';   
                            }
                            $catHtml .= '<div class="padding-zero root-col-1 clearfix">';
                                if ($left_width != 0 || $left_sub_width != 0) {
                                    $left_sub_content = $this->getBlockContent($load_cat->getData('megamenu_type_leftblock'));
                                    if ($left_sub_content != '') {
                                        $catHtml .= '<div class="'.$sub_left_content_area.' clearfix rootmegamenu_block">';
                                            $catHtml .= $left_sub_content;
                                        $catHtml .= '</div>';   
                                    } else {
                                        $catHtml .= '<div class="'.$left_content_area.' clearfix rootmegamenu_block">';
                                            $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_leftblock'));
                                        $catHtml .= '</div>';
                                    }
                                }
                                $catHtml .= '<div class="'.$category_area_width.' clearfix padding-zero'.$masonryClass.'">';
                                    $sub_cnt = 0;
                                    // 3th Level Category
                                    foreach ($products as $_product) {
                                        $brake_point = $colnum * 3;
                                        if ($sub_cnt > $brake_point) { continue; }
                                        $sub_cnt++;
                                        
                                        $catHtml .= '<div class="root-col-'.$colnum.'">';
                                            $catHtml .= '<div class="htabproductbxleft clearfix"><a href='.$_product->getProductUrl().' class="tabimtag"><img src='.$media_url.'catalog/product'.$_product->getImage().' alt="'.$main_cat->getName().'"/></a></div>';
                                            $catHtml .= '<div class="htabproductbxright clearfix">';
                                                $catHtml .= '<div class="htabproductbxhead">'.$_product->getName().'</div>';
                                                $catHtml .= '<div class="htabproductbxprice">'.__('Price:').'<span> '.$currency.number_format($_product['price'],2).'</span></div>';
                                                $catHtml .= '<a href='.$_product->getProductUrl().' class="htabproductbxcartbtn">'.__('View Product').'</a>';
                                            $catHtml .= '</div>';
                                        $catHtml .= '</div>';
                                        
                                        if ($sub_cnt%$colnum==0) {
                                            $catHtml .= '<div class="clearfix"></div>';
                                        }
                                    }
                                    if ($sub_cnt > $brake_point) {
                                        $catHtml .= '<a href='.$load_cat->getURL().' class="view_all">View All &raquo;</a>';
                                    }
                                $catHtml .= '</div>';
                                if ($right_width != 0 || $right_sub_width != 0) {
                                    $right_sub_content = $this->getBlockContent($load_cat->getData('megamenu_type_rightblock'));
                                    if ($right_sub_content != '') {
                                        $catHtml .= '<div class="'.$sub_right_content_area.' clearfix rootmegamenu_block">';
                                            $catHtml .= $right_sub_content;
                                        $catHtml .= '</div>';   
                                    } else {
                                        $catHtml .= '<div class="'.$right_content_area.' clearfix rootmegamenu_block">';
                                            $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_rightblock'));
                                        $catHtml .= '</div>';
                                    }
                                }
                            $catHtml .= '</div>';
                            if ( $load_cat->getMegamenuTypeFooter() != '' ) {
                                $catHtml .= '<div class="menufooter root-col-1 clearfix">';
                                    $catHtml .= $this->getBlockContent($load_cat->getData('megamenu_type_footer'));
                                $catHtml .= '</div>';   
                            } elseif( $main_cat->getMegamenuTypeFooter() != '' ) {
                                $catHtml .= '<div class="menufooter root-col-1 clearfix">';
                                    $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_footer'));
                                $catHtml .= '</div>';   
                            } else { }
						$catHtml .= '</div>';
					 } else {
						$catHtml .= '<div class="root-col-75 verticalopen empty_category">';
						    $catHtml .= '<span>Product does not found for '.$load_cat->getName().' Category</span>';
						$catHtml .= '</div>';
                    }
					$catHtml .= '</li>';
				}
			$catHtml .= '</ul>';
			$catHtml .= '</div>';
			$catHtml .= '</div></div>';
		}
		return $catHtml;	
	}
    
    
    /**
     * Act HTML Block.
     */ 
    public function act()
	{
        $dt_db_blank = $this->_scopeConfig->getValue('rootmegamenu_option/general/lcstatus');
        if ($dt_db_blank == '') {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $isMultiStore =  $this->_scopeConfig->getValue('rootmegamenu_option/general/ismultistore');
            $u = $this->_storeManager->getStore()->getBaseUrl();
            if ($isMultiStore == 1)  {
                $u = $objectManager->create('Magento\Backend\Helper\Data')->getHomePageUrl();
            }
            $l = $this->_scopeConfig->getValue('rootmegamenu_option/general/licencekey');
            $surl = base64_decode($this->_customhelper->surl());
            $url = $surl."?u=".$u."&l=".$l."&extname=m2_megamenu";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch);
            curl_close($ch);
            $act_data = json_decode($result, true);
            if (isset($act_data['status']) && $act_data['status'] == '0') {
                return "PGRpdiBzdHlsZT0iYmFja2dyb3VuZDogYmxhY2sgbm9uZSByZXBlYXQgc2Nyb2xsIDAlIDAlOyBmbG9hdDogbGVmdDsgcGFkZGluZzogMTBweDsgd2lkdGg6IDEwMCU7IGNvbG9yOiByZWQ7IiBpZD0ibm90X2FjdGl2YXRlZCI+SXNzdWUgd2l0aCB5b3VyIFJvb3R3YXlzIG1lZ2EgbWVudSBleHRlbnNpb24gbGljZW5zZSBrZXksIHBsZWFzZSBjb250YWN0IDxhIGhyZWY9Im1haWx0bzpoZWxwQHJvb3R3YXlzLmNvbSI+aGVscEByb290d2F5cy5jb208L2E+PC9kaXY+";
            } else {
                $this->_customresourceConfig->saveConfig('rootmegamenu_option/general/lcstatus', $l, 'default', 0);
            }
        }
	}
	
    /**
     * Contact Us Mega Menu HTML Block.
     */   
	public function contactus()
	{
        $contact_form_value = $this->_customhelper->getConfig('rootmegamenu_option/general/show_contactus');
        $contact_form_col = 'root-col-1';
        
		$catHtml = '';
		$catHtml .= '<li class="contactus_menu"><a href="javascript:void(0);">'.__('Contact Us').'</a>';
			$catHtml .= '<div class="megamenu fullmenu contacthalfmenu clearfix">';
            $catHtml .= '<div class="'.$this->_customhelper->menuContainerClass().'">';
                if ($contact_form_value == 2) {
                    $catHtml .= '<div class="root-col-2 clearfix">';
                        $contact_content = $this->_customhelper->getConfig('rootmegamenu_option/general/contactus_content');
                        $catHtml .= $this->getBlockContent($contact_content);
                    $catHtml .= '</div>';
                    $contact_form_col = 'root-col-2';
                }
                $base_url = $this->_storeManager->getStore()->getBaseUrl();
					
				$catHtml .= '<div class="'.$contact_form_col.' clearfix">';
					$catHtml .= '<div class="title">'.__('Contact Us').'</div>';
					$catHtml .=	'<form id="megamenu_contact_form" name="megamenu_contact_form" class="menu_form">';
						$catHtml .= '<input id="name" name="name" type="text" autocomplete="off" placeholder="'.__('Name').'">';
						$catHtml .= '<input id="menuemail" name="menuemail" type="text" autocomplete="off" placeholder="'.__('Email').'">';
						$catHtml .= '<input type="text" title="Telephone" id="telephone" name="telephone" autocomplete="off" placeholder="'.__('Telephone').'">';
						$catHtml .= '<textarea id="comment" name="comment" placeholder="'.__('Your message...').'"></textarea>';
						$catHtml .= '<input type="text" style="display:none !important;" value="" id="hideit" name="hideit">';
						$catHtml .= '<input type="text" style="display:none !important;" value="'.$base_url.'" name="base_url" id="base_url" >';
						$catHtml .= '<input onclick="rootFunction()" type="button" value="'.__('Reset').'">';
						$catHtml .= '<input id="megamenu_submit" type="submit" value="'.__('Send').'">';
					$catHtml .= '</form>';
				$catHtml .= '</div>';
			$catHtml .= '</div></div>';
		$catHtml .= '</li>';
			
		return $catHtml;
	}
    
    /*
    public function getValuesConfig()
    {
        return $this->_scopeConfig->getValue('rootmegamenu_option/general/licencekey');
    }
    */
    
    public function _getMenuItemAttributes($item)
    {
        $menuItemClasses = $this->_getMenuItemClasses($item);
        return implode(' ', $menuItemClasses);
    }
    
    /**
     * Get Class of categories.
     */  
    protected function _getMenuItemClasses($item)
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $classes = [];
        if ($this->_customhelper->getConfig('rootmegamenu_option/general/topmenuarrow') == 1) {
            if ($item->hasChildren()) {
                $classes[] = 'has-sub-cat';
            }
        }
        
        /*
        $classes[] = 'rootlevel' . $item->getLevel();
        $classes[] = $item->getPositionClass();
        */
        
        $request = $_objectManager->get('Magento\Framework\App\Action\Context')->getRequest();
        if ($request->getFullActionName() == 'catalog_category_view') {
            $cur_cat = $_objectManager->get('Magento\Framework\Registry')->registry('current_category');
            $categoryPathIds = explode(',', $cur_cat->getPathInStore());
            if (in_array($item->getId(), $categoryPathIds) == '1') {
                $classes[] = 'active';   
            }
        }
        return $classes;
    }
    
    /**
     * Get Custom Links
     */  
    public function getCustomLinks($category_id)
    {
        $base_url = rtrim($this->_storeManager->getStore()->getBaseUrl(),'/');
		$customMenus = $this->_customhelper->getConfig('rootmegamenu_option/general/custom_link', $this->_customhelper->getStoreId());
        $customLinkHtml = '';
        if (!empty($customMenus) && $customMenus != '[]') {
            $customMenus = $this->_customhelper->getJsonDecode($customMenus);
            /*
            if ($this->_customhelper->getMagentoVersion() >= '2.2.0') {
                $customMenus = json_decode($customMenus, true);
            } else {
                $customMenus =  \Magento\Framework\Serialize\SerializerInterface::unserialize($customMenus);
            }*/
            if ( is_array($customMenus) ) {
                foreach ($customMenus as $customMenusRow) {
                    if ($customMenusRow['custommenulink'] != '') {
                        if (substr($customMenusRow['custommenulink'], 0, 1) != '/') {
                            $no_custom_link = $customMenusRow['custommenulink'];
                        } else {
                            $no_custom_link = $base_url.$customMenusRow['custommenulink'];
                        }
                    } else {
                        $no_custom_link = 'javascript:void(0);';
                    }
                    if (isset($customMenusRow['custom_menu_position'])) {
                        if ( $customMenusRow['custom_menu_position'] == $category_id  && $customMenusRow['custom_menu_position'] != '' ) {
                            $customLinkHtml .= $this->getCustomDropDownContent($no_custom_link, $customMenusRow);
                            /*
                            $customLinkHtml .= '<li class="custom-menus"><a href="'.$no_custom_link.'">'.$customMenusRow['custommenuname'].'</a>';
                            if ($customMenusRow['custom_menu_block'] != '') {
                                $customLinkHtml .= $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($customMenusRow['custom_menu_block'])->toHtml();
                            }
                            $customLinkHtml .= '</li>';
                            */
                        }

                        if ($category_id == false &&
                            ($customMenusRow['custom_menu_position'] == 'default' ||
                             $customMenusRow['custom_menu_position'] == 'right' ||
                             $customMenusRow['custom_menu_position'] == 'left')
                           ) {
                            $customLinkHtml .= $this->getCustomDropDownContent($no_custom_link, $customMenusRow);
                            /*
                            $customLinkHtml .= '<li class="custom-menus"><a href="'.$no_custom_link.'">'.$customMenusRow['custommenuname'].'</a>';
                            if ($customMenusRow['custom_menu_block'] != '') {
                                $customLinkHtml .= $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($customMenusRow['custom_menu_block'])->toHtml();
                            }
                            $customLinkHtml .= '</li>';
                            */
                        }
                    }
                    
                }
            }
        }
        return $customLinkHtml;
    }
    
    protected function getCustomDropDownContent($no_custom_link, $customMenusRow)
    {
        $dropDownHtml = '';
        $dropdownClass = 'custom-menus';
        if ($customMenusRow['custom_menu_block'] != '') {
            $content = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($customMenusRow['custom_menu_block'])->toHtml();
            if (!empty($customMenusRow['custom_menu_layout'])) {
                if ($customMenusRow['custom_menu_layout'] == 4) {
                    $dropDownHtml = '<ul class="rootmenu-submenu">'.$content.'</ul>';
                } else if ($customMenusRow['custom_menu_layout'] == 5) {
                    $dropDownHtml = '<ul class="rootmenu-submenu dropdown-leftside">'.$content.'</ul>';
                    $dropdownClass .= ' position-relative';
                } else  if ($customMenusRow['custom_menu_layout'] == 2) {
                    $dropDownHtml = '<div class="halfmenu clearfi">'.$content.'</div>';
                } else  if ($customMenusRow['custom_menu_layout'] == 3) {
                    $dropDownHtml = '<div class="halfmenu clearfi dropdown_left">'.$content.'</div>';
                    $dropdownClass .= ' dropdown_left';
                } else {
                    $dropDownHtml = '<div class="megamenu fullmenu clearfix categoriesmenu">'.$content.'</div>';
                }
            } else {
                $dropDownHtml = '<div class="megamenu fullmenu clearfix categoriesmenu">'.$content.'</div>';
            }
        }
        if ($customMenusRow['custom_menu_position'] == 'left' || $customMenusRow['custom_menu_position'] == 'right') {
            $dropdownClass .= ' rwcustomlink-'.$customMenusRow['custom_menu_position'];
        }
        $result = '<li class="'.$dropdownClass.'"><a href="'.$no_custom_link.'">'.$customMenusRow['custommenuname'].'</a>'.$dropDownHtml.'</li>';
        return $result;
    }
    
    /**
     * Get Social Share Links
     */  
    public function getSocialShare()
    {
        $html = '';
        if ($this->_customhelper->getConfig('rootmegamenu_option/general/show_social_share_icon')) {
            $fb = $this->_customhelper->getConfig('rootmegamenu_option/general/fb');
            $twitter = $this->_customhelper->getConfig('rootmegamenu_option/general/twitter');
            $gplus = $this->_customhelper->getConfig('rootmegamenu_option/general/gplus');
            $youtube = $this->_customhelper->getConfig('rootmegamenu_option/general/youtube');
            $vimeo = $this->_customhelper->getConfig('rootmegamenu_option/general/vimeo');
            $instagram = $this->_customhelper->getConfig('rootmegamenu_option/general/instagram');
            $pinterest = $this->_customhelper->getConfig('rootmegamenu_option/general/pinterest');
            $skype = $this->_customhelper->getConfig('rootmegamenu_option/general/skype');
            $emailid = $this->_customhelper->getConfig('rootmegamenu_option/general/emailid');
            $phone = $this->_customhelper->getConfig('rootmegamenu_option/general/phone');
            
            if ($fb != '') {
                $html .= '<a class="rw-fb" target="_blank" href="'.$fb.'"><span>Facebook</span></a>';
            }
            if ($twitter != '') {
                $html .= '<a class="rw-twitter" target="_blank" href="'.$twitter.'"><span>Twitter</span></a>';
            }
            if ($gplus != '') {
                $html .= '<a class="rw-gplus" target="_blank" href="'.$gplus.'"><span>Google Plus</span></a>';
            }
            if ($youtube != '') {
                $html .= '<a class="rw-youtube" target="_blank" href="'.$youtube.'"><span>Youtube</span></a>';
            }
            if ($vimeo != '') {
                $html .= '<a class="rw-vimeo" target="_blank" href="'.$vimeo.'"><span>Vimeo</span></a>';
            }
            if ($instagram != '') {
                $html .= '<a class="rw-instagram" target="_blank" href="'.$instagram.'"><span>Instagram</span></a>';
            }
            if ($pinterest != '') {
                $html .= '<a class="rw-pinterest" target="_blank" href="'.$pinterest.'"><span>Pinterest</span></a>';
            }
            if ($skype != '') {
                $html .= '<a class="rw-skype" target="_blank" href="skype:'.$skype.'?call"><span>Skype</span></a>';
            }
            if ($emailid != '') {
                $html .= '<a class="rw-emailid" target="_blank" href="mailto:'.$emailid.'"><span>Email</span></a>';
            }
            if ($phone != '') {
                $html .= '<a class="rw-phone" target="_blank" href="tel:'.$phone.'"><span>Phone</span></a>';
            }
        }
        $socialHtml = '';
        if ($html != '') {
            $socialHtml = '<li class="rw-social-links">'.$html.'</li>';
        }
        return $socialHtml;
    }
    
    protected function masonryCategoryClass($cId)
    {
        $enableMasonry = $this->_customhelper->manageMasonry();
        $masonryClass = '';
        $colClass = 'root-col-';
        if ($enableMasonry == 1) {
            $masonryClass = ' grid';
            $colClass = 'grid-item-';
        } else if ($enableMasonry == 2) {
            $masonryCategories = $this->_customhelper->masonryCategory();
            if (in_array($cId, $masonryCategories)) {
                $masonryClass = ' grid';
                $colClass = 'grid-item-';
            }
        } else {
            $masonryClass = '';
            $colClass = 'root-col-';
        }
        $msClasses = array($masonryClass, $colClass);
        return $msClasses;
    }
    
    public function AllCategoryHalfMenu($main_cat, $navCnt0)
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        $colnum = 2;
        /*
        $colnum = $main_cat->getMegamenuTypeNumofcolumns();
		if ($colnum == 0) {
            $colnum = 2;
        }
        */
        $left_width = $main_cat->getMegamenuTypeLeftblockW();
        $right_width = $main_cat->getMegamenuTypeRightblockW();
        $cat_width = 12 - ($left_width + $right_width);
        $category_area_width = 'root-sub-col-'.$cat_width;
        $left_content_area = 'root-sub-col-'.$left_width;
        $right_content_area = 'root-sub-col-'.$right_width;
        $catHtml = '';
        $childrenCategories = $main_cat->getChildrenCategories();
		if (count($childrenCategories)) {
			$catHtml .= '<div class="v_halfmenu varticalmenu_main halfwidth clearfix">';
                $catHtml .= '<div class="root-col-1 clearfix">';
                    $catHtml .= '<div class="root-col-'.$colnum.' clearfix">';
                        $catHtml .= '<ul class="level2-popup">';
                        $cnt = 1;
                        $cat_tot = count($childrenCategories);
                        $brk = ceil($cat_tot/$colnum);
                        // 2nd Level Category
                        $navCnt = 0;
                        foreach ($childrenCategories as $childCategory) {
                            $navCnt++;
                            $load_cat = $_objectManager->create('Magento\Catalog\Model\Category')->load($childCategory->getId());
                            $load_cat->getCollection()->addAttributeToFilter('include_in_menu', '1');
                             
                            if ($main_cat->getMegamenuShowCatimage() == 1) {
                                if ($this->_customhelper->getMegaMenuImageName($load_cat) != '') {
                                    $imageurl = $this->_customhelper->getMegaMenuImageUrl($load_cat);
                                } else {
                                    $imageurl = $this->getViewFileUrl('Rootways_Megamenu::images/rootimgicon.jpg');
                                }
                                $image_html = '<span class="vertical-listing-img"><img style="width:'.$main_cat->getMegamenuShowCatimageWidth().'px; height:'.$main_cat->getMegamenuShowCatimageHeight().'px;" src='.$imageurl.' alt=""/></span>';
                                $line_height =  'style="line-height:'.$main_cat->getMegamenuShowCatimageHeight().'px;"';
            
                            } else { 
                                $image_html  = '<i aria-hidden="true" class="verticalmenu-arrow fa fa-angle-right"></i>';
                                $line_height =  '';
                            }
                            /*
                            $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.' category-item"><a href='.$load_cat->getURL().' '.$line_height.'>';
                            $catHtml .= $image_html;
                            $catHtml .= $load_cat->getName().'</a></li>';
                            */
                            
                            $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.' category-item"><a href='.$load_cat->getURL().'>';
                            $catHtml .= $image_html;
                            $catHtml .= '<span class="sub-cat-name" style="height:'.$main_cat->getMegamenuShowCatimageWidth().'px;">'.$load_cat->getName();
                            if ( $load_cat->getMegamenuTypeLabeltx() != '' ) {
                                $catHtml .= '<span class="top-sub-label"><em class="rootmenutag" style="background-color: #'.$load_cat->getMegamenuTypeLabelclr().'">'.$load_cat->getMegamenuTypeLabeltx().'</em></span>';
                            }
                            $catHtml .= '</span>';
                            $childrenCategories_2 = $load_cat->getChildrenCategories();
                            if (count($childrenCategories_2)) {
                                $catHtml .= '<span class="cat-arrow"></span>';
                            }
                            $catHtml .='</a>';
                            if (count($childrenCategories_2)) {
                                $catHtml .= '<span class="rootmenu-click"><i class="rootmenu-arrow"></i></span>';
                                $catHtml .= '<ul class="level3-popup halfwidth-popup-sub-sub">';
                                    $navCnt1 = 0;
                                    foreach ($childrenCategories_2 as $childCategory2) {
                                        $navCnt1++;
                                        $load_cat_sub = $_objectManager->create('Magento\Catalog\Model\Category')->load($childCategory2->getId());
                                        $load_cat_sub->getCollection()->addAttributeToFilter('include_in_menu', '1');
                                        if ($main_cat->getMegamenuShowCatimage() == 1) {
                                            if ($this->_customhelper->getMegaMenuImageName($load_cat_sub) != '') { 
                                                $imageurl_sub = $this->_customhelper->getMegaMenuImageUrl($load_cat_sub);
                                            } else {
                                                $imageurl_sub = $this->getViewFileUrl('Rootways_Megamenu::images/rootimgicon.jpg');
                                            }
                                            $image_html_sub = '<img style="width:25px; height:25px;" src='.$imageurl_sub.' alt=""/>';
                                        } else { 
                                            $image_html_sub = ''; 
                                        }

                                        $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.'-'.$navCnt1.' category-item"><a class="clearfix" href='.$load_cat_sub->getURL().'>';
                                        $catHtml .= $image_html_sub;
                                        $catHtml .= '<span class="level3-name">'.$load_cat_sub->getName().'</span>';
                                        $catHtml .= '</a></li>';
                                    }
                                $catHtml .= '</ul>';
                            }

                            $catHtml .=  '</li>';
                            if ( $cnt%$brk == 0 && $cnt != $cat_tot ) { $catHtml .= '</ul></div><div class="root-col-'.$colnum.' clearfix"><ul class="level2-popup">'; }
                            $cnt ++;
                        }
                        $catHtml .= '</ul>';
                    $catHtml .= '</div>';
                $catHtml .= '</div>';
			$catHtml .= '</div>';
		}
		return $catHtml;
    }
    
    public function AllCategoryHalfTitleMenu($main_cat, $navCnt0)
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $viewMoreAfter = $main_cat->getMegamenuTypeViewmore();
        $colnum = $this->_customhelper->getConfig('rootmegamenu_option/general/show_all_columns');
        $width = $this->_customhelper->getConfig('rootmegamenu_option/general/show_all_width');
        $widthClass = 'v_halfmenu varticalmenu_main halfwidth clearfix';
        if ($width == '2') {
            $widthClass = 'vertical_fullwidthmenu varticalmenu_main fullwidth clearfix';
        }
        $showIconLevl2 = $this->_customhelper->getConfig('rootmegamenu_option/general/show_all_level2_icon');
        $showIcon = $this->_customhelper->getConfig('rootmegamenu_option/general/display_sub_cat_icon');
        $media_url = $_objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        
        $left_width = $main_cat->getMegamenuTypeLeftblockW();
        $right_width = $main_cat->getMegamenuTypeRightblockW();
        $cat_width = 12 - ($left_width + $right_width);
        $category_area_width = 'root-sub-col-'.$cat_width;
        $left_content_area = 'root-sub-col-'.$left_width;
        $right_content_area = 'root-sub-col-'.$right_width;
        $catHtml = '';
        $childrenCategories = $main_cat->getChildrenCategories();
		if (count($childrenCategories)) {
            $catHtml .= '<div class="'.$widthClass.'">';
                $catHtml .= '<div class="root-col-1 clearfix grid">';
                    // 2nd Level Category
                    $navCnt = 0;
                    foreach ($childrenCategories as $childCategory) {
                        $catHtml .= '<div class="grid-item-'.$colnum.' clearfix">';
                        $navCnt++;
                        $load_cat = $this->categoryRepository->get($childCategory->getId(), $this->_customhelper->getStoreId());
                        
                        $image_html = '';
                        if ($showIconLevl2 == 1 && $this->_customhelper->getMegaMenuImageName($load_cat) != '') {
                            $imageurl = $this->_customhelper->getMegaMenuImageUrl($load_cat);
                            $style = '';
                            if ($main_cat->getMegamenuShowCatimageWidth() != '' && $main_cat->getMegamenuShowCatimageHeight() != '') {
                                $style = 'style="width:'.$main_cat->getMegamenuShowCatimageWidth().'px; height:'.$main_cat->getMegamenuShowCatimageHeight().'px;"';    
                            }
                            $image_html = '<a href='.$load_cat->getURL().' class="catproductimg"><img  '.$style.' src='.$imageurl.' alt="'.$load_cat->getName().'"/></a>';
                        }
                        //$catHtml .= $this->getImageHtmlAsTitle($main_cat, $load_cat, 1);
                        $catHtml .= $image_html;
                        
                        $catHtml .= '<div class="title_normal"><a href='.$load_cat->getURL().'>'.$load_cat->getName().'</a>';
                        if ($load_cat->getMegamenuTypeLabeltx() != '') {
                            $catHtml .= '<span class="top-sub-label"><em class="rootmenutag" style="background-color: #'.$load_cat->getMegamenuTypeLabelclr().'">'.$load_cat->getMegamenuTypeLabeltx().'</em></span>';
                        }
                        $catHtml .= '</div>';
                        $childrenCategories_2 = $load_cat->getChildrenCategories();
                        if (count($childrenCategories_2)) {
                            $catHtml .= '<span class="vertical-click"><i class="verticalmenu-arrow fa fa-angle-down" aria-hidden="true"></i></span>';
                            $catHtml .= '<ul class="level3-popup halfwidth-popup-sub-sub">';
                                $navCnt1 = 0;
                                foreach ($childrenCategories_2 as $childCategory2) {
                                    if ($navCnt1 >= $viewMoreAfter && $viewMoreAfter != '') {
                                        $catHtml .= '<li><a class="view-more" href='.$load_cat->getURL().'>'.__('View More').'</a></li>';
                                        break;
                                    }
                                    $navCnt1++;
                                    $load_cat_sub = $this->categoryRepository->get($childCategory2->getId(), $this->_customhelper->getStoreId());
                                    //$image_html_sub = $this->getImageHtml($main_cat, $load_cat_sub, 2);
                                    if ($showIcon == 1) {
                                        $image_html_sub = '<span class="rw_allsubcat_arrow"></span>';
                                    } elseif ($showIcon == 2) {
                                        $subCatIcon = $this->_customhelper->getConfig('rootmegamenu_option/general/sub_category_icon');
                                        if ($subCatIcon != '') {
                                            $image_html_sub = '<img class="rw_allsubcat_icon" style="width:25px; height:25px;" src="'.$media_url.'rootways/images/'.$subCatIcon.'" alt="'.$load_cat_sub->getName().'">';
                                        }
                                    } else {
                                        $image_html_sub = '';
                                    }
                                    $catHtml .= '<li class="nav-'.$navCnt0.'-'.$navCnt.'-'.$navCnt1.' category-item"><a class="clearfix" href='.$load_cat_sub->getURL().'>';
                                    $catHtml .= $image_html_sub;
                                    $catHtml .= '<span class="level3-name">'.$load_cat_sub->getName().'</span>';
                                    $catHtml .= '</a></li>';
                                }
                            $catHtml .= '</ul>';
                        }
                        $catHtml .= '</div>';
                    }
                $catHtml .= '</div>';
			$catHtml .= '</div>';
		}
		return $catHtml;
    }
    
    public function viewAllCategoriesHTML()
    {
        $catHtml = '';
        if ($this->_customhelper->getConfig('rootmegamenu_option/general/show_all_category') == 1 || 
            $this->_customhelper->getConfig('rootmegamenu_option/general/show_all_category') == 2
           ) {
            $catHtml .= '
            <li class="rw-vertical-menu all-category-wrapper">
                <span class="rootmenu-click"><i class="rootmenu-arrow"></i></span>
                <a class="has-sub-cat" href="javaScript:void(0);"
                title="'.$this->_customhelper->getConfig('rootmegamenu_option/general/all_category_title').'">'.$this->_customhelper->getConfig('rootmegamenu_option/general/all_category_title').'
                </a>
                <div class="verticalmenu02 clearfix">
                    <ul class="vertical-list clearfix">';
                        $main_all_cat_cnt = 0;
                        $viewAllCat = '';
                        if ($this->_customhelper->getConfig('rootmegamenu_option/general/show_all_category') == 2) {
                            $selectedCategoriesArray = '';
                            $selectedCategories = $this->_customhelper->getConfig('rootmegamenu_option/general/show_all_category_selected_categories');
                            if ($selectedCategories != '') {
                                $viewAllCat = explode(',', $selectedCategories);
                            }
                        } else {
                            $viewAllCategory = $this->_customhelper->getCategory($this->_customhelper->getRootCategoryId());
                            $viewAllCat = $viewAllCategory->getChildrenCategories();
                        }
                        foreach ($viewAllCat as $category) {
                            $main_all_cat_cnt++;
                            if (gettype($category) == 'object') {
                                $all_cat_category_load = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($category->getId());
                            } else {
                                $all_cat_category_load = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($category);
                            }
                            $custom_cls_all_cat = 'nav-'.$main_all_cat_cnt.' category-item';
                            $catHtml .= '<li class="rootverticalnav '.$custom_cls_all_cat.'">
                                <a href="'.$all_cat_category_load->getURL().'">';
                                    if ($this->_customhelper->getConfig('rootmegamenu_option/general/show_all_category_icon') == 1 &&
                                              $all_cat_category_load->getImageUrl() != '') {
                                        $catHtml .= '<span class="main-category-name">
                                            <i class="main-category-icon"><img src="'.$all_cat_category_load->getImageUrl().'"/></i>
                                            <em>'.$all_cat_category_load->getName().'</em>
                                        </span>';
                                    } else {
                                        $catHtml .= $all_cat_category_load->getName();
                                    }
                                $catHtml .= '</a>';
                                //$catHtml .= $this->AllCategoryHalfMenu($category, $main_all_cat_cnt);
                                $catHtml .= $this->AllCategoryHalfTitleMenu($all_cat_category_load, $main_all_cat_cnt);
                            $catHtml .= '</li>';
                        }
                    $catHtml .= '</ul>
                </div>
            </li>';
        }
        return $catHtml;
    }
    
    /*
    public function viewAllCategoriesHTML()
    {
        $catHtml = '';
        if ($this->_customhelper->getConfig('rootmegamenu_option/general/show_all_category') == 1 || 
            $this->_customhelper->getConfig('rootmegamenu_option/general/show_all_category') == 2
           ) {
            $catHtml .= '
            <li class="rw-vertical-menu all-category-wrapper">
                <span class="rootmenu-click"><i class="rootmenu-arrow"></i></span>
                <a class="has-sub-cat" href="javaScript:void(0);"
                title="'.$this->_customhelper->getConfig('rootmegamenu_option/general/all_category_title').'">'.$this->_customhelper->getConfig('rootmegamenu_option/general/all_category_title').'
                </a>';
            
                        $viewAllCat = '';
                        if ($this->_customhelper->getConfig('rootmegamenu_option/general/show_all_category') == 2) {
                            $selectedCategoriesArray = '';
                            $selectedCategories = $this->_customhelper->getConfig('rootmegamenu_option/general/show_all_category_selected_categories');
                            if ($selectedCategories != '') {
                                $viewAllCat = explode(',', $selectedCategories);
                            }
                        } else {
                            $viewAllCategory = $this->_customhelper->getCategory($this->_customhelper->getRootCategoryId());
                            $viewAllCat = $viewAllCategory->getChildrenCategories();
                        }
                        $catHtml .= $this->getMultiTabbing(1, $viewAllCat, 0);
            $catHtml .= '</li>';
        }
        return $catHtml;
    }
    */
}
