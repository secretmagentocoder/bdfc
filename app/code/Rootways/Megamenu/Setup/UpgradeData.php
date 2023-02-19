<?php
namespace Rootways\Megamenu\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Category setup factory
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;
    
    /**
     * Init
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
         $this->categorySetupFactory = $categorySetupFactory;
    }
    
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addNewCategoryAttributes($setup);
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->addNewCategoryAttributes($setup);
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->addNewViewMoreAttribute($setup);
        }
        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $this->addImageOnlyMegaMenu($setup);
        }
        $installer->endSetup();
    }
    
    private function addNewCategoryAttributes(ModuleDataSetupInterface $setup)
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);

        $menu_attributes = [
             'megamenu_type_half_pos' => [
                'type' => 'varchar',
                'label' => 'Dropdown Position',
                'input' => 'select',
                'source' => 'Rootways\Megamenu\Model\Attribute\Dropdownpos',
                'required' => false,
                'sort_order' => 160,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ]
        ];

        foreach ($menu_attributes as $item => $data) {
            $categorySetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, $item, $data);
        }
        $idg =  $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Rootways Mega Menu');
        foreach ($menu_attributes as $item => $data) {
            $categorySetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $idg,
                $item,
                $data['sort_order']
            );
        }
        
    }
    
    private function addNewViewMoreAttribute(ModuleDataSetupInterface $setup)
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);

        $menu_attributes = [
             'megamenu_type_viewmore' => [
                'type' => 'varchar',
                'label' => 'Add View More Link After',
                'input' => 'text',
                'required' => false,
                'sort_order' => 170,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_type_subcatlevel' => [
                'type' => 'varchar',
                'label' => 'Level of Sub Categories to be Display',
                'input' => 'select',
                'source' => 'Rootways\Megamenu\Model\Attribute\SubCatLevel',
                'required' => false,
                'sort_order' => 180,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
        ];

        foreach ($menu_attributes as $item => $data) {
            $categorySetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, $item, $data);
        }
        $idg =  $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Rootways Mega Menu');
        foreach ($menu_attributes as $item => $data) {
            $categorySetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $idg,
                $item,
                $data['sort_order']
            );
        }
    }
    
    private function addImageOnlyMegaMenu(ModuleDataSetupInterface $setup)
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);

        $menu_attributes = [
             'megamenu_type_showtitle' => [
                'type' => 'int',
                'label' => 'Show Title With Image',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'required' => false,
                'sort_order' => 190,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_type_subcol' => [
                'type' => 'varchar',
                'label' => 'Number of Columns Under One Title',
                'input' => 'select',
                'source' => 'Rootways\Megamenu\Model\Attribute\Numberofcol',
                'required' => false,
                'sort_order' => 200,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_type_imgpos' => [
                'type' => 'varchar',
                'label' => 'Image Position',
                'input' => 'select',
                'source' => 'Rootways\Megamenu\Model\Attribute\ImgPosition',
                'required' => false,
                'sort_order' => 210,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ]
        ];

        foreach ($menu_attributes as $item => $data) {
            $categorySetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, $item, $data);
        }
        $idg =  $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Rootways Mega Menu');
        foreach ($menu_attributes as $item => $data) {
            $categorySetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $idg,
                $item,
                $data['sort_order']
            );
        }
    }
}
