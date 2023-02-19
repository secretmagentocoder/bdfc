<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\Simpledetailconfigurable\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateCustomUrlData implements DataPatchInterface
{
    /**
     * @var \Bss\Simpledetailconfigurable\Helper\AdditionalInfoSaving
     */
    private $additionalInfoSaving;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * UpgradeData constructor.
     *
     * @param \Bss\Simpledetailconfigurable\Helper\AdditionalInfoSaving $additionalInfoSaving
     * @param \Magento\Framework\App\State $state
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\AdditionalInfoSaving $additionalInfoSaving,
        \Magento\Framework\App\State $state,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->additionalInfoSaving = $additionalInfoSaving;
        $this->state = $state;
        $this->logger = $logger;
    }

    /**
     * Update custom url dataa
     *
     * @return void
     */
    public function apply()
    {
        try {
            $areaCode = $this->state->getAreaCode();

            if ($areaCode === null) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
        } catch (\Magento\Framework\Exception\LocalizedException|\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        $this->additionalInfoSaving->updateCustomUrlData();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
