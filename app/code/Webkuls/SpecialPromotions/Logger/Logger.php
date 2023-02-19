<?php
/**
 * Webkulss Software.
 *
 * @category  Webkulss
 * @package   Webkulss_SpecialPromotions
 * @author    Webkulss
 * @copyright Copyright (c) Webkulss Software Private Limited (https://Webkuls.com)
 * @license   https://store.Webkuls.com/license.html
 */
namespace Webkuls\SpecialPromotions\Logger;

class Logger extends \Monolog\Logger
{
    /**
     * @param string             $name       The logging channel
     * @param HandlerInterface[] $handlers   Optional stack of handlers
     * @param callable[]         $processors Optional array of processors
     */
    public function __construct($name, $handlers = [], $processors = [])
    {
        $this->name = $name;
        $this->handlers = $handlers;
        $this->processors = $processors;
    }
}
