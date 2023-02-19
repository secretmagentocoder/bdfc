<?php
namespace Rootways\Megamenu\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class InstallData implements InstallDataInterface
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
    
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
         
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
         
        $menu_attributes = [
            'megamenu_type' => [
                'type' => 'varchar',
                'label' => 'Mega Menu Type',
                'input' => 'select',
                'source' => 'Rootways\Megamenu\Model\Attribute\Menutype',
                'required' => false,
                'sort_order' => 10,
                'input_renderer' => 'Rootways\Megamenu\Block\Adminhtml\Category\Helper\Dependency',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_type_numofcolumns' => [
                'type' => 'varchar',
                'label' => 'Sub-category Columns',
                'input' => 'select',
                'source' => 'Rootways\Megamenu\Model\Attribute\Numberofcol',
                'required' => false,
                'sort_order' => 20,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_show_catimage' => [
                'type' => 'int',
                'label' => 'Show Category Image',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'required' => false,
                'sort_order' => 30,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_show_catimage_img' => [
                'type' => 'varchar',
                'label' => 'Category Image',
                'input' => 'image',
                'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
                'required' => false,
                'sort_order' => 40,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_show_catimage_width' => [
                'type' => 'varchar',
                'label' => 'Category Image Width',
                'input' => 'text',
                'required' => false,
                'sort_order' => 50,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_show_catimage_height' => [
                'type' => 'varchar',
                'label' => 'Category Image Height',
                'input' => 'text',
                'required' => false,
                'sort_order' => 60,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_type_leftblock' => [
                'type' => 'text',
                'label' => 'Left Side Content Block',
                'input' => 'textarea',
                'required' => false,
                'sort_order' => 70,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_type_leftblock_w' => [
                'type' => 'varchar',
                'label' => 'Left Side Block Width',
                'input' => 'select',
                'source' => 'Rootways\Megamenu\Model\Attribute\ContentBlockWidth',
                'required' => false,
                'sort_order' => 80,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_type_rightblock' => [
                'type' => 'text',
                'label' => 'Right Side Content Block',
                'input' => 'textarea',
                'required' => false,
                'sort_order' => 90,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_type_rightblock_w' => [
                'type' => 'varchar',
                'label' => 'Right Side Block Width',
                'input' => 'select',
                'source' => 'Rootways\Megamenu\Model\Attribute\ContentBlockWidth',
                'required' => false,
                'sort_order' => 100,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_type_header' => [
                'type' => 'text',
                'label' => 'Header Content Block',
                'input' => 'textarea',
                'required' => false,
                'sort_order' => 110,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
             'megamenu_type_footer' => [
                'type' => 'text',
                'label' => 'Footer Content Block',
                'input' => 'textarea',
                'required' => false,
                'sort_order' => 120,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_type_labeltx' => [
                'type' => 'varchar',
                'label' => 'Label Text',
                'input' => 'text',
                'required' => false,
                'sort_order' => 130,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_type_labelclr' => [
                'type' => 'varchar',
                'label' => 'Label Color',
                'input' => 'text',
                'required' => false,
                'sort_order' => 140,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Rootways Menu'
            ],
            'megamenu_type_class' => [
                'type' => 'varchar',
                'label' => 'Custom Class',
                'input' => 'text',
                'required' => false,
                'sort_order' => 150,
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
        $installer->endSetup();
    }
}
