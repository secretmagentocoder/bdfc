<?php
/**
 * Mega Menu CustomLinks Block.
 *
 * @category  Site Search & Navigation
 * @package   Rootways_Megamenu
 * @author    Developer RootwaysInc <developer@rootways.com>
 * @copyright 2021 Rootways Inc. (https://www.rootways.com)
 * @license   Rootways Custom License
 * @link      https://www.rootways.com/pub/media/extension_doc/license_agreement.pdf
 */
namespace Rootways\Megamenu\Block\Adminhtml\System\Config;

class CustomLinks extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    protected $_itemRenderer;
    
    protected $_categoryRenderer;
    
    protected $_dropdownLayoutRenderer;
    
    protected function _prepareToRender()
    {
        $this->addColumn('custommenuname', ['label' => __('Menu Name'),  'renderer' => false]);
        $this->addColumn('custommenulink', ['label' => __('Menu Link'),  'renderer' => false]);
        $this->addColumn(
            'custom_menu_block',
            ['label' => __('Dropdown Content'), 'renderer' => $this->_getDropdownRenderer()]
        );
        $this->addColumn(
            'custom_menu_layout',
            ['label' => __('Dropdown Layout'), 'renderer' => $this->_getDropdownLayoutRenderer()]
        );
        $this->addColumn(
            'custom_menu_position',
            ['label' => __('Position Before'), 'renderer' => $this->_getPositionRenderer()]
        );
 
        $this->_addButtonLabel = __('Add New Custom Menu');
    }
    
    /**
     * Retrieve Statick Block renderer
     *
     * @return StaticBlock
     */
    protected function _getDropdownRenderer()
    {
        if (!$this->_itemRenderer) {
            $this->_itemRenderer = $this->getLayout()->createBlock(
                'Rootways\Megamenu\Block\Adminhtml\System\Config\CustomMenuBlock',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_itemRenderer->setClass('customer_group_select');
        }
        
        return $this->_itemRenderer;
    }
    
    /**
     * Retrieve Category renderer
     *
     * @return Categories
    */
    protected function _getPositionRenderer()
    {
        if (!$this->_categoryRenderer) {
            $this->_categoryRenderer = $this->getLayout()->createBlock(
                'Rootways\Megamenu\Block\Adminhtml\System\Config\CustomMenuCategory',
                '',
                ['data' =>  ['is_render_to_js_template' => true]]
            );
            $this->_categoryRenderer->setClass('customer_category_select');
        }
        
        return $this->_categoryRenderer;
    }
    
    /**
     * Retrieve Dropdown Layout renderer
     *
     * @return array
    */
    protected function _getDropdownLayoutRenderer()
    {
        if (!$this->_dropdownLayoutRenderer) {
            $this->_dropdownLayoutRenderer = $this->getLayout()->createBlock(
                'Rootways\Megamenu\Block\Adminhtml\System\Config\CustomMenuLayout',
                '',
                ['data' =>  ['is_render_to_js_template' => true]]
            );
            $this->_dropdownLayoutRenderer->setClass('customer_category_select');
        }
        
        return $this->_dropdownLayoutRenderer;
    }
   
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' .
            $this->_getDropdownRenderer()->calcOptionHash($row->getData('custom_menu_block'))] =
            'selected="selected"';
        $optionExtraAttr['option_' .
            $this->_getDropdownLayoutRenderer()->calcOptionHash($row->getData('custom_menu_layout'))] =
            'selected="selected"';
        $optionExtraAttr['option_' .
            $this->_getPositionRenderer()->calcOptionHash($row->getData('custom_menu_position'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
   
}
