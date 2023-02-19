<?php

namespace Ecommage\CheckoutData\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;

class CreateCollectionPointBlock implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * AddAccessViolationPageAndAssignB2CCustomers constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param PageFactory $blockFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $newCmsStaticBlock = [
            'title' => 'Collection Point',
            'identifier' => 'collection_point',
            'content' => '<style>#html-body [data-pb-style=DB0FTWQ],#html-body [data-pb-style=GTK2QRW]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=MTYDAQM]{border-style:none}#html-body [data-pb-style=MQXMCW8],#html-body [data-pb-style=RMYEONY]{max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=MTYDAQM]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="GTK2QRW"><div data-content-type="text" data-appearance="default" data-element="main"><p>Click &amp; Collect orders can be submitted up to 6 hour(s) prior to your departure. All dates and times are calculated using the local time in Bahrain (GMT +3 hours).<br><br></p></div></div></div><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="DB0FTWQ"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="MTYDAQM"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/emirates-logo.png}}" alt="" title="" data-element="desktop_image" data-pb-style="MQXMCW8"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/emirates-logo.png}}" alt="" title="" data-element="mobile_image" data-pb-style="RMYEONY"></figure><div data-content-type="text" data-appearance="default" data-element="main"><p>Collection Point<br>Departure&nbsp;The Pearl Lounge<br>You can collect your order from the webshop closest to your boarding gate if you are a departing passenger or from the webshop in the terminal of arrival if you are an arrival passenger.</p></div></div></div>',
            'is_active' => 1,
            'stores' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
        ];

        $this->moduleDataSetup->startSetup();

        /** @var \Magento\Cms\Model\Block $block */
        $block = $this->blockFactory->create();
        $block->setData($newCmsStaticBlock)->save();

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
