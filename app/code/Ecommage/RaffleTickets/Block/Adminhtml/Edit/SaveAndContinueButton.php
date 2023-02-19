<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Ecommage RaffleTickets Admin Save And continue button
 */
class SaveAndContinueButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get botton Data
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndContinueEdit'],
                ],
            ],
            'sort_order' => 80,
        ];
    }
}
