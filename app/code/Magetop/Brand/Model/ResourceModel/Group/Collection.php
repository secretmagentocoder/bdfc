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
namespace Magetop\Brand\Model\ResourceModel\Group;

use \Magetop\Brand\Model\ResourceModel\AbstractCollection;
/**
 * Brand collection
 */
class Collection extends AbstractCollection
{

	/**
     * @var string
     */
	protected $_idFieldName = 'group_id';

	/**
     * Define resource model
     *
     * @return void
     */
	protected function _construct()
	{
		$this->_init('Magetop\Brand\Model\Group', 'Magetop\Brand\Model\ResourceModel\Group');
		$this->_map['fields']['group_id'] = 'main_table.group_id';
	}

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
}