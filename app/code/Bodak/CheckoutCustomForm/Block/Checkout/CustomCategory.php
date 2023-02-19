<?php

namespace Bodak\CheckoutCustomForm\Block\Checkout;

use Magento\Framework\View\Element\Template;

class CustomCategory extends Template
{
    protected $_template = 'Bodak_CheckoutCustomForm::checkout/form-checkout.phtml';
    protected $arr = [];

    public function __construct
    (
        \ExperiencesDigital\CustomCalculation\Model\ResourceModel\CustomCalculation\CollectionFactory $collectionFactory,
        \Bodak\CheckoutCustomForm\Helper\Data $helper,
        Template\Context                      $context,
        array                                 $data = []
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }


    public function getCustomCategory()
    {
        $category = $this->getCustomCategoryCalculation();
        if ($category) {
            foreach ($category as $item) {
                $param = $item->getData();
                {
                    if (!in_array($param['Parent_Custom_Category'],array_column($this->arr,'name')))
                    $this->arr[] = [
                        'name' => empty($param['Parent_Custom_Category']) ?  $param['Code'] : $param['Parent_Custom_Category'],
                        'code' => str_replace(' ', '_', $param['Code']),
                        'description' => $param['Description'],
                        'parent' => $param['Parent_Custom_Category'],
                        'size' => $param['Limit_UOM']
                    ];
                }

            }
        }

        return $this->arr;
    }


    public function getCustomCategoryCalculation()
    {
        return $this->collectionFactory->create()
                    ->addFieldToFilter('Active',1);
    }
}
