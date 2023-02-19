<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Ui\Component\Listing\RaffleTickets\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Show extends Column
{
    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$items) {
                if ($items['show_winner'] == 0) {
                    $items['show_winner'] = "Disabled";
                } else {
                    $items['show_winner'] = "Enabled";
                }
            }
        }
        return $dataSource;
    }
}
