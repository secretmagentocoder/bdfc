<?php

namespace Amasty\Orderattr\Block\Adminhtml\Attribute\Edit;

class Js extends \Magento\Backend\Block\Template
{
    /**
     * @var \Amasty\Orderattr\Model\Attribute\InputType\InputTypeProvider
     */
    private $inputTypeProvider;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\Orderattr\Model\Attribute\InputType\InputTypeProvider $inputTypeProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->inputTypeProvider = $inputTypeProvider;
    }

    /**
     * @return \Amasty\Orderattr\Model\Attribute\InputType\InputType[]|array
     */
    public function getAttributeInputTypes()
    {
        return $this->inputTypeProvider->getList();
    }

    /**
     * @return array
     */
    public function getAttributeInputTypesWithOptions()
    {
        return $this->inputTypeProvider->getInputTypesWithOptions();
    }

    /**
     * @param mixed $row
     *
     * @return string
     */
    public function encode($row)
    {
        return \Zend_Json::encode($row);
    }
}
