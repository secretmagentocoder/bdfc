<?php
namespace Custom\CartRule\Plugin\Model;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;

class Product
{
    /**
     * @var State
     */
    private $state;

    public function __construct(
        State $state
    ) {
        $this->state = $state;
    }

    public function afterGetPrice(\Magento\Catalog\Model\Product $subject, $result)
    {
        $product_id = $subject->getId();
        $product_excise_duty = $subject->getExciseDuty();
        $product_excise_duty_price = $subject->getExciseDutyPrice();

        if ($product_excise_duty == true && is_numeric($product_excise_duty_price)) {
            if ($this->state->getAreaCode() === Area::AREA_FRONTEND) {
                $result += $product_excise_duty_price;
            }
        }
        return $result;
    }
    
    public function afterGetSpecialPrice(\Magento\Catalog\Model\Product $subject, $result)
    {
        $product_id = $subject->getId();
        $product_excise_duty = $subject->getExciseDuty();
        $product_excise_duty_price = $subject->getExciseDutyPrice();

        if ($product_excise_duty == true && is_numeric($product_excise_duty_price) && !empty($result) && $result != 0) {
            if ($this->state->getAreaCode() === Area::AREA_FRONTEND) {
                $result += $product_excise_duty_price;
            }
        }
        return $result;
    }
}