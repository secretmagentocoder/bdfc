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
use Magento\Framework\Data\OptionSourceInterface;

class IsActive implements OptionSourceInterface
{
	/**
	 * @var \Magetop\Brand\Model\Brand
	 */
	protected $brandModel;

	/**
     * Constructor
     *
     * @param \Magetop\Brand\Model\Brand $brandModel
     */
	public function __construct(\Magetop\Brand\Model\Brand $brandModel)
	{
		$this->brandModel = $brandModel;
	}

	/**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->brandModel->getAvailableStatuses();

        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
 
        return $options;
    }
}