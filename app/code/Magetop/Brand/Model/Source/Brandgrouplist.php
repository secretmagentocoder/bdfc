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
namespace Magetop\Brand\Model\Source;

class Brandgrouplist implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magetop\Brand\Model\Group
     */
    protected  $_group;
    
    /**
     * 
     * @param \Magetop\Brand\Model\Group $group
     */
    public function __construct(
        \Magetop\Brand\Model\Group $group
        ) {
        $this->_group = $group;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $groups = $this->_group->getCollection()
        ->addFieldToFilter('status', '1');
        $groupList = array();
        foreach ($groups as $group) {
            $groupList[] = array('label' => $group->getName(),
                'value' => $group->getId());
        }
        return $groupList;
    }
}
