<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Ecommage_RaffleTickets Admin Edit Back Button
 */
class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get botton Data
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getUrl('ecommage_raffle_tickets/raffleticket/index')),
            'class' => 'back',
            'sort_order' => 10
        ];
    }
}
