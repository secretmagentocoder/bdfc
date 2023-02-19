<?php

namespace ExperiencesDigital\CustomCalculation\Model;

class CustomCalculation extends \Magento\Framework\Model\AbstractModel
{
	const CACHE_TAG = 'custom_category_calculation';

	protected $_cacheTag = 'custom_category_calculation';

	protected $_eventPrefix = 'custom_category_calculation';

    protected function _construct()
    {
        $this->_init(\ExperiencesDigital\CustomCalculation\Model\ResourceModel\CustomCalculation::class);
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];
		return $values;
    }
}
