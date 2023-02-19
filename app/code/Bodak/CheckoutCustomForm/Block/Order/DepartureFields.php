<?php
/**
 * DepartureFields Block.
 *
 * @package   Bodak\CheckoutCustomForm
 * @author    Slawomir Bodak <slawek.bodak@gmail.com>
 * @copyright © 2017 Slawomir Bodak
 * @license   See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Bodak\CheckoutCustomForm\Block\Order;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Bodak\CheckoutCustomForm\Api\Data\DepartureFieldsInterface;
use Bodak\CheckoutCustomForm\Api\DepartureFieldsRepositoryInterface;

/**
 * Class DepartureFields
 *
 * @category Block/Order
 * @package  Bodak\CheckoutCustomForm\Block
 */
class DepartureFields extends Template
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * DepartureFieldsRepositoryInterface
     *
     * @var DepartureFieldsRepositoryInterface
     */
    protected $departureFieldsRepository;

    /**
     * DepartureFields constructor.
     *
     * @param Context                         $context                Context
     * @param Registry                        $registry               Registry
     * @param DepartureFieldsRepositoryInterface $departureFieldsRepository DepartureFieldsRepositoryInterface
     * @param array                           $data                   Data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DepartureFieldsRepositoryInterface $departureFieldsRepository,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->departureFieldsRepository = $departureFieldsRepository;
        $this->_isScopePrivate = true;
        $this->_template = 'order/view/departure_fields.phtml';
        parent::__construct($context, $data);
    }

    /**
     * Get current order
     *
     * @return Order
     */
    public function getOrder() : Order
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Get checkout Departure fields
     *
     * @param Order $order Order
     *
     * @return DepartureFieldsInterface
     */
    public function getDepartureFields(Order $order)
    {
        return $this->departureFieldsRepository->getDepartureFields($order);
    }
}
