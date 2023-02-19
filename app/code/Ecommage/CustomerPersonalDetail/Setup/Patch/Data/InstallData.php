<?php

namespace Ecommage\CustomerPersonalDetail\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallData implements DataPatchInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Ecommage\CustomerPersonalDetail\Model\Config\DataCountry $dataCountry
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Ecommage\CustomerPersonalDetail\Model\Config\DataCountry $dataCountry
    ) {
        $this->dataCountry = $dataCountry;
        $this->moduleDataSetup = $moduleDataSetup;

    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return InstallData|void
     */
    public function apply()
    {
        $data = [];
        $this->moduleDataSetup->startSetup();
        $setup = $this->moduleDataSetup;

        foreach ($this->dataCountry->getOption() as $key => $value){
            $data[] = ['code_id' => $key, 'name' => $value];
        }
        $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable('ecommage_nation_code'),
            ['code_id', 'name'],
            $data
        );
        $this->moduleDataSetup->endSetup();
    }
}
