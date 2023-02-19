<?php

namespace Ecommage\CreateProductApi\Cron;

class WebValueMappingProduct
{

    public function __construct
    (
        \Ecommage\CreateProductApi\Helper\Data $helper
    )
    {
        $this->helper = $helper;
    }


    public function execute()
    {
        $this->helper->setVariant();
    }
}