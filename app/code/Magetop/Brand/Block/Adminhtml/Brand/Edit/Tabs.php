<?php
/**
 * Magetop
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Magetop
 * @package    Magetop_Brand
 * @copyright  Copyright (c) 2014 Magetop (https://www.magetop.com/)
 * @license    https://www.magetop.com/LICENSE.txt
 */
namespace Magetop\Brand\Block\Adminhtml\Brand\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Brand Information'));
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareLayout()
    {
        $this->addTab(
                'general',
                [
                    'label' => __('Brand Information'),
                    'content' => $this->getLayout()->createBlock('Magetop\Brand\Block\Adminhtml\Brand\Edit\Tab\Main')->toHtml()
                ]
            );

        $this->addTab(
                'products',
                [
                    'label' => __('Products'),
                    'url' => $this->getUrl('magetopbrand/*/products', ['_current' => true]),
                    'class' => 'ajax'
                ]
            );

        $this->addTab(
                'design',
                [
                    'label' => __('Design'),
                    'content' => $this->getLayout()->createBlock('Magetop\Brand\Block\Adminhtml\Brand\Edit\Tab\Design')->toHtml()
                ]
            );

        $this->addTab(
                'meta',
                [
                    'label' => __('Meta Data'),
                    'content' => $this->getLayout()->createBlock('Magetop\Brand\Block\Adminhtml\Brand\Edit\Tab\Meta')->toHtml()
                ]
            );
    }
}
