<?php

namespace Ecommage\RaffleTickets\Model;

class OptionCmsPage extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    public function __construct
    (
        \Magento\Cms\Model\PageFactory $pageFactory
    )
    {
        $this->pageFactory = $pageFactory;
    }

    public function getAllOptions()
    {
        $option = $this->pageFactory->create();
        $arr = [];
        foreach ($option->getCollection() as $item)
        {
            $arr[] = ['label' => __('Select...') , 'value' => ''];
            $arr[] =
            [
                'label' => __($item->getTitle()),
                'value' => $item->getIdentifier()
            ];
        }
        
        return $arr;
    }
}