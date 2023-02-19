<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ecommage\CustomerCategory\Model;

/**
 * Catalog category landing page attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => 0, 'label' => __('None')],
                ['value' => 1, 'label' => __('Cash Raffle')],
                ['value' => 2, 'label' => __('Car Raffle')],
                ['value' => 3, 'label' => __('Bike Raffle')],
            ];
        }
        return $this->_options;
    }
}
