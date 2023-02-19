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
namespace Magetop\Brand\Model\Config\Source;
class Staticblock implements \Magento\Framework\Option\ArrayInterface
{
	protected  $_blockModel;

    /**
     * @param \Magento\Cms\Model\Block $blockModel
     */
    public function __construct(
    	\Magento\Cms\Model\Block $blockModel
    	) {
    	$this->_groupModel = $blockModel;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
    	$collection = $this->_groupModel->getCollection();
    	$blocks = array();
    	foreach ($collection as $_block) {
    		$blocks[] = [
    		'value' => $_block->getId(),
    		'label' => addslashes($_block->getTitle())
    		];
    	}
        $blocks[] = [
                'value' => 'pretext_html',
                'label' => 'Pretext HTML'];
        array_unshift($blocks, array(
                'value' => '',
                'label' => '-- Please Select --',
                ));
        return $blocks;
    }
}