<?php
/**
 * Webkuls Software.
 *
 * @category  Webkuls
 * @package   Webkuls_SpecialPromotions
 * @author    Webkuls
 * @copyright Copyright (c) Webkuls Software Private Limited (https://Webkuls.com)
 * @license   https://store.Webkuls.com/license.html
 */
namespace Webkuls\SpecialPromotions\Logger;

use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * Log File name
     * @var string
     */
    protected $fileName = '/var/log/SpecialPromotions.log';
}
