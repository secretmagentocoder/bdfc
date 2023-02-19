<?php

namespace Ecommage\RaffleTickets\Block\Raffle;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class ChangeNumber extends Template implements BlockInterface
{
    protected $_template = 'Ecommage_RaffleTickets::category/raffle/search/view-number.phtml';

    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
}
