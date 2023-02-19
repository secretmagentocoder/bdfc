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
namespace Magetop\Brand\Block\Widget;

class AbstractWidget extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
	/**
	 * @var \Magetop\Brand\Helper\Data
	 */
	protected $_brandHelper;

	/**
     * @param \Magento\Framework\View\Element\Template\Context $context     
     * @param \Magetop\Brand\Helper\Data                           $brandHelper 
     * @param array                                            $data        
     */
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetop\Brand\Helper\Data $brandHelper,
        array $data = []
        ) {
        $this->_brandHelper = $brandHelper;
        parent::__construct($context, $data);
    }

    public function getConfig($key, $default = '')
    {
        if($this->hasData($key))
        {
            return $this->getData($key);
        }
        return $default;
    }
}