<?php

namespace Ecommage\NavSyncPromotion\Cron;

use Ecommage\NavSyncPromotion\Helper\Data;

class SyncSalesRule
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * SyncSalesRule constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * call this method if want to run once for all
     *
     * To split and run many times, you need to add limit parameter for each run
     * for example: $this->helperData->navSyncOffer(0,100);
     * @inheritdoc
     */
    public function execute()
    {
        $this->helperData->navSyncOffer();
    }
}
