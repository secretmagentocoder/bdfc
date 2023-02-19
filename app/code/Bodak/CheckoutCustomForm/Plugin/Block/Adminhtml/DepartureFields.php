<?php
/**
 * @package   Bodak\CheckoutCustomForm
 * @author    Slawomir Bodak <slawek.bodak@gmail.com>
 * @copyright © 2017 Slawomir Bodak
 * @license   See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Bodak\CheckoutCustomForm\Plugin\Block\Adminhtml;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Block\Adminhtml\Order\View\Info;
use Bodak\CheckoutCustomForm\Api\DepartureFieldsRepositoryInterface;

/**
 * Class DepartureFieldsRepository
 *
 * @category Adminhtml/Plugin
 * @package  Bodak\CheckoutCustomForm\Plugin
 */
class DepartureFields
{
    /**
     * DepartureFieldsRepositoryInterface
     *
     * @var DepartureFieldsRepositoryInterface
     */
    protected $departureFieldsRepository;

    /**
     * DepartureFields constructor.
     *
     * @param DepartureFieldsRepositoryInterface $departureFieldsRepository Repository Interface
     */
    public function __construct(DepartureFieldsRepositoryInterface $departureFieldsRepository)
    {
        $this->departureFieldsRepository = $departureFieldsRepository;
    }

    /**
     * Modify after to html.
     *
     * @param Info   $subject Info
     * @param string $result  Result
     *
     * @return string
     * @throws LocalizedException
     */
    public function afterToHtml(Info $subject, $result) {
        $block = $subject->getLayout()->getBlock('order_departure_fields');
        if ($block !== false) {
            $block->setOrderDepartureFields(
                $this->departureFieldsRepository->getDepartureFields($subject->getOrder())
            );
            $block->setCurrentOrder(
                $subject->getOrder()
            );
            $result = $result . $block->toHtml();
        }

        return $result;
    }
}
