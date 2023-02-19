<?php

namespace Ecommage\RaffleTickets\Block\Adminhtml;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Element\Template;

class WidgetUpload extends \Magento\Framework\View\Element\Template
{
   public function __construct(Template\Context $context, array $data = [])
   {
       parent::__construct($context, $data);
   }
}