<?php

namespace Amasty\Orderattr\Block\Adminhtml\Attribute\Edit\Tab\Options;

class Tooltip extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Amasty_Orderattr::attribute/tooltip.phtml');
    }

    /**
     * Retrieve stores collection with default store
     *
     * @return array
     */
    public function getStores()
    {
        if (!$this->hasStores()) {
            $this->setData('stores', $this->_storeManager->getStores(true));
        }
        return $this->_getData('stores');
    }

    /**
     * Retrieve frontend labels of attribute for each store
     *
     * @return array
     */
    public function getTooltipValues()
    {
        $values = [];
        $storeLabels = $this->getAttributeObject()->getStoreTooltips();
        foreach ($this->getStores() as $store) {
            $values[$store->getId()] = isset($storeLabels[$store->getId()]) ? $storeLabels[$store->getId()] : '';
        }

        return $values;
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return \Amasty\Orderattr\Model\Attribute\Attribute
     */
    private function getAttributeObject()
    {
        return $this->registry->registry('entity_attribute');
    }
}
