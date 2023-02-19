<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Webhook
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Model;

use Magento\Framework\Model\AbstractModel;
use Mageplaza\Webhook\Helper\Data;

/**
 * Class Hook
 * @package Mageplaza\Webhook\Model
 */
class CronSchedule extends AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_webhook_cron_schedule';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_webhook_cron_schedule';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_webhook_cron_schedule';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\CronSchedule::class);
    }

    /**
     * @return AbstractModel
     */
    protected function _afterLoad()
    {
        if (!is_array($this->getHeaders())) {
            $value = $this->getHeaders();
            $this->setHeaders(empty($value) ? false : Data::jsonDecode($value));
        }

        return parent::_afterLoad(); // TODO: Change the autogenerated stub
    }
}
