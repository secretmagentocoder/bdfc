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
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getId()) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\''
                    . __('Are you sure you want to delete this Block?')
                    . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * Get Delete Url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/raffleticket/deletebutton', ['id' => $this->getId()]);
    }
}
