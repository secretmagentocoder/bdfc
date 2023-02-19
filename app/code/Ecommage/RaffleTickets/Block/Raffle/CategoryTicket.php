<?php

namespace Ecommage\RaffleTickets\Block\Raffle;

use Magento\Backend\Model\View\Result\Page;
use Magento\Catalog\Block\Product\View\Options\Type\Select\MultipleFactory;
use Magento\Catalog\Pricing\Price\CalculateCustomOptionCatalogRule;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Widget\Block\BlockInterface;

class CategoryTicket extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions implements BlockInterface
{
    protected $_template = 'Ecommage_RaffleTickets::category/raffle/view.phtml';

    public function __construct(
        Page $page,
        \Magento\Framework\Registry                      $registry,
        MultipleFactory                                  $multipleFactory = null,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data           $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        array                                            $data = [],
        CalculateCustomOptionCatalogRule                 $calculateCustomOptionCatalogRule = null,
        CalculatorInterface                              $calculator = null,
        PriceCurrencyInterface                           $priceCurrency = null
    ) {
        $this->page = $page;
        $this->_registry       = $registry;
        $this->multipleFactory = $multipleFactory ?: ObjectManager::getInstance()->get(MultipleFactory::class);
        parent::__construct($context, $pricingHelper, $catalogData, $data, $calculateCustomOptionCatalogRule, $calculator, $priceCurrency);
    }

    public function getBlockOption()
    {
        return $this->page->getLayout()->createBlock('Ecommage\RaffleTickets\Block\Raffle\RaffleOptions');
    }

}
