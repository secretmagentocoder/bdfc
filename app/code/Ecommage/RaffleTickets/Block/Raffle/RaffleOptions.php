<?php

namespace Ecommage\RaffleTickets\Block\Raffle;

use Magento\Catalog\Model\Product\Option\Value;

class RaffleOptions extends \Magento\Catalog\Block\Product\View\Options
{
    public function getJsonConfig()
    {
        $config = [];
        foreach ($this->getOptions() as $option) {
            /* @var $option \Magento\Catalog\Model\Product\Option */
            if ($option->hasValues()) {
                $tmpPriceValues = [];
                foreach ($option->getValues() as $valueId => $value) {
                    $tmpPriceValues[$valueId] = $this->_getPriceConfiguration($value);
                }
                $priceValue = $tmpPriceValues;
            } else {
                $priceValue = $this->_getPriceConfiguration($option);
            }
            $config[$option->getId()] = $priceValue;
        }

        $configObj = new \Magento\Framework\DataObject(
            [
                'config' => $config,
            ]
        );

        $config = $configObj->getConfig();

        return $this->_jsonEncoder->encode($config);
    }

     /**
     * Get price configuration
     *
     * @param \Magento\Catalog\Model\Product\Option\Value|\Magento\Catalog\Model\Product\Option $option
     * @return array
     */
    protected function _getPriceConfiguration($option)
    {$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/_getPriceConfiguration.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info('text message');
        $optionPrice = $option->getPrice(true);
        if ($option->getPriceType() !== Value::TYPE_PERCENT) {
            $optionPrice = $this->pricingHelper->currency($optionPrice, false, false);
        }
        $optionTaxPrice = $this->getProduct()->getFinalPrice();
        // $logger->info('text message'.$optionTaxPrice);

        $optionPrice = $this->getProduct()->getPrice();
        $data = [
            'prices' => [
                'oldPrice' => [
                    'amount' => $this->pricingHelper->currency($option->getRegularPrice(), false, false),
                    'adjustments' => [],
                ],
                'basePrice' => [
                    'amount' => $this->_catalogData->getTaxPrice(
                        $option->getProduct(),
                        $optionPrice,
                        false,
                        null,
                        null,
                        null,
                        null,
                        null,
                        false
                    ),
                ],
                'finalPrice' => [
                    'amount' => $this->_catalogData->getTaxPrice(
                        $option->getProduct(),
                        $optionTaxPrice,
                        true,
                        null,
                        null,
                        null,
                        null,
                        null,
                        false
                    ),
                ],
            ],
            'type' => $option->getPriceType(),
            'name' => $option->getTitle(),
        ];
        $logger->info(print_r($data, true));
        return $data;
    }


}
