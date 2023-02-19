<?php
namespace Amasty\Orderattr\Block\Adminhtml\Order\Create\Form\Attributes\Data;

use Magento\Framework\Data\Form as FrameworkForm;

class Form extends FrameworkForm
{
    /**
     * Escape suffix for file input
     *
     * @param string $suffix
     * @return $this
     */
    public function addFieldNameSuffix($suffix)
    {
        foreach ($this->_allElements as $element) {
            if ($element->getType() === 'file') {
                continue;
            }
            $name = $element->getName();
            if ($name) {
                $element->setName($this->addSuffixToName($name, $suffix));
            }
        }
        return $this;
    }
}
