<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Created By : Rohan Hapani
 */
declare (strict_types = 1);
namespace Ecommage\CustomerCategory\Setup\Patch\Data;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
class CreatePageRaffleTicket implements DataPatchInterface
{
    /**
     * ModuleDataSetupInterface
     *
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param PageFactory $pageFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        PageFactory $pageFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->pageFactory = $pageFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $cmsPage = $this->pageFactory->create();
        $cmsPage->load('raffle-ticket');
        if (!$cmsPage->getId()) {
            $content = '
        <style>#html-body [data-pb-style=JTKNY6E]{border-color:#000}#html-body [data-pb-style=XBOGCPH]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=EDASCRY]{min-height:300px}#html-body [data-pb-style=XLCGSQI]{background-position:left top;background-size:cover;background-repeat:no-repeat;min-height:300px}#html-body [data-pb-style=VQR4PTB]{min-height:300px;background-color:transparent}#html-body [data-pb-style=KD4F376]{background-position:left top;background-size:cover;background-repeat:no-repeat;min-height:300px}#html-body [data-pb-style=W0PV42F]{min-height:300px;background-color:transparent}#html-body [data-pb-style=M9R4SET]{background-position:left top;background-size:cover;background-repeat:no-repeat;min-height:300px}#html-body [data-pb-style=XWW6D4Y]{min-height:300px;background-color:transparent}#html-body [data-pb-style=R7EEL3I]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;min-height:100px;width:16.6667%;align-self:flex-start}#html-body [data-pb-style=JABHEU8]{display:flex;margin:0;flex-direction:column}#html-body [data-pb-style=B0IPG2G]{display:inline-block}#html-body [data-pb-style=B44927X]{text-align:center}#html-body [data-pb-style=CEPD9XV]{display:inline-block}#html-body [data-pb-style=ICG3MFS]{text-align:center}#html-body [data-pb-style=IOM5KQJ]{display:inline-block}#html-body [data-pb-style=H984AH3]{text-align:center}#html-body [data-pb-style=E55JR34]{display:inline-block}#html-body [data-pb-style=AYWNBHQ]{text-align:center}#html-body [data-pb-style=LOTOWXD]{display:inline-block}#html-body [data-pb-style=H8BUG9L]{text-align:center}#html-body [data-pb-style=C1M7MYL],#html-body [data-pb-style=J2VSL8U]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:83.3333%;align-self:stretch}#html-body [data-pb-style=C1M7MYL]{border-width:100px;border-radius:50px;width:100%}#html-body [data-pb-style=JGB3QWC]{text-align:center}</style><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="JTKNY6E">&lt;h3 class="fw-bold text-uppercase text-center mb-4 py-5 mt-4  "&gt;&lt;/span&gt;BUY TICKET, GET LUCKY &amp; WIN BIG&lt;/span&gt;&lt;/h3&gt;</div><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="XBOGCPH"><div class="pagebuilder-slider" data-content-type="slider" data-appearance="default" data-autoplay="false" data-autoplay-speed="4000" data-fade="false" data-infinite-loop="true" data-show-arrows="true" data-show-dots="false" data-element="main" data-pb-style="EDASCRY"><div data-content-type="slide" data-slide-name="" data-appearance="poster" data-show-button="never" data-show-overlay="never" data-element="main"><div data-element="empty_link"><div class="pagebuilder-slide-wrapper" data-background-images="{\&quot;desktop_image\&quot;:\&quot;{{media url=wysiwyg/banner2hhhhh.jpg}}\&quot;}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="wrapper" data-pb-style="XLCGSQI"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="" data-element="overlay" data-pb-style="VQR4PTB"><div class="pagebuilder-poster-content"><div data-element="content"></div></div></div></div></div></div><div data-content-type="slide" data-slide-name="" data-appearance="poster" data-show-button="never" data-show-overlay="never" data-element="main"><div data-element="empty_link"><div class="pagebuilder-slide-wrapper" data-background-images="{\&quot;desktop_image\&quot;:\&quot;{{media url=wysiwyg/banner2hhhhh.jpg}}\&quot;}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="wrapper" data-pb-style="KD4F376"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="" data-element="overlay" data-pb-style="W0PV42F"><div class="pagebuilder-poster-content"><div data-element="content"></div></div></div></div></div></div><div data-content-type="slide" data-slide-name="" data-appearance="poster" data-show-button="never" data-show-overlay="never" data-element="main"><div data-element="empty_link"><div class="pagebuilder-slide-wrapper" data-background-images="{\&quot;desktop_image\&quot;:\&quot;{{media url=wysiwyg/2022-09-14_18-12-08_4.jpg}}\&quot;}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="wrapper" data-pb-style="M9R4SET"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="" data-element="overlay" data-pb-style="XWW6D4Y"><div class="pagebuilder-poster-content"><div data-element="content"></div></div></div></div></div></div></div></div></div><div class="pagebuilder-column-group" style="display: flex;" data-content-type="column-group" data-grid-size="12" data-element="main"><div class="pagebuilder-column" data-content-type="column" data-appearance="align-top" data-background-images="{}" data-element="main" data-pb-style="R7EEL3I"><div data-content-type="buttons" data-appearance="stacked" data-same-width="true" data-element="main" data-pb-style="JABHEU8"><div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="B0IPG2G"><a class="pagebuilder-button-link" href="https://www.google.com/search?q=edit+cms+page+in+magento+2&amp;oq=edit+cms+page+in+magento+2+&amp;aqs=chrome..69i57j0i22i30l3j0i390l2j69i60l2.29943j0j7&amp;sourceid=chrome&amp;ie=UTF-8" target="" data-link-type="default" data-element="link" data-pb-style="B44927X"><span data-element="link_text">BUY &amp; WIN LUXURY CAR</span></a></div></div><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main"><div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="CEPD9XV"><a class="pagebuilder-button-link" href="https://www.google.com/search?q=edit+cms+page+in+magento+2&amp;oq=edit+cms+page+in+magento+2+&amp;aqs=chrome..69i57j0i22i30l3j0i390l2j69i60l2.29943j0j7&amp;sourceid=chrome&amp;ie=UTF-8" target="" data-link-type="default" data-element="link" data-pb-style="ICG3MFS"><span data-element="link_text">BUY CASH RAFFLES TICKET</span></a></div></div><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main"><div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="IOM5KQJ"><a class="pagebuilder-button-link" href="https://www.google.com/search?q=edit+cms+page+in+magento+2&amp;oq=edit+cms+page+in+magento+2+&amp;aqs=chrome..69i57j0i22i30l3j0i390l2j69i60l2.29943j0j7&amp;sourceid=chrome&amp;ie=UTF-8" target="" data-link-type="default" data-element="link" data-pb-style="H984AH3"><span data-element="link_text">RECENT WINER</span></a></div></div><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main"><div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="E55JR34"><a class="pagebuilder-button-link" href="https://www.google.com/search?q=edit+cms+page+in+magento+2&amp;oq=edit+cms+page+in+magento+2+&amp;aqs=chrome..69i57j0i22i30l3j0i390l2j69i60l2.29943j0j7&amp;sourceid=chrome&amp;ie=UTF-8" target="" data-link-type="default" data-element="link" data-pb-style="AYWNBHQ"><span data-element="link_text">NEW EVENT</span></a></div></div><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main"><div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="LOTOWXD"><a class="pagebuilder-button-link" href="https://www.google.com/search?q=edit+cms+page+in+magento+2&amp;oq=edit+cms+page+in+magento+2+&amp;aqs=chrome..69i57j0i22i30l3j0i390l2j69i60l2.29943j0j7&amp;sourceid=chrome&amp;ie=UTF-8" target="" data-link-type="default" data-element="link" data-pb-style="H8BUG9L"><span data-element="link_text">FAQ</span></a></div></div></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="J2VSL8U"><div data-content-type="html" data-appearance="default" data-element="main">{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" title="BUY TICKET" products_count="3" template="Ecommage_CustomerCategory::widget/raffle_ticket.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`visibility`,`operator`:`==`,`value`:`4`^]^]"}}</div></div></div><div class="pagebuilder-column-group" style="display: flex;" data-content-type="column-group" data-grid-size="12" data-element="main"><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="C1M7MYL"><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="JGB3QWC">{{widget type="Ecommage\CustomerCategory\Block\Posts" draw_image="https://bahrain.mgt/admin/cms/wysiwyg/directive/___directive/e3ttZWRpYSB1cmw9Ind5c2l3eWcvQWRfQmFubmVyX0xlZnRfMS5qcGcifX0,/" draw_link="https://bahrain.mgt/raffle-ticket" winner_image="https://bahrain.mgt/admin/cms/wysiwyg/directive/___directive/e3ttZWRpYSB1cmw9Ind5c2l3eWcvQWRfQmFubmVyX0xlZnRfMi5qcGcifX0,/" winner_link="https://bahrain.mgt/raffle-ticket"}}</div></div></div>
        ';

            $pageData = [
                'title' => 'Raffle Ticket',
                'page_layout' => '1column',
                'meta_keywords' => 'RH Cms Meta Keywords',
                'meta_description' => 'RH Cms Meta Description',
                'identifier' => 'raffle-ticket',
                'content_heading' => '',
                'content' => $content,
                'layout_update_xml' => '',
                'url_key' => 'raffle-ticket',
                'is_active' => 1,
                'stores' => [0, 1],
                'sort_order' => 0,
            ];
            $this->moduleDataSetup->startSetup();
            $cmsPage->setData($pageData)->save();
            $this->moduleDataSetup->endSetup();
        }
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
    public function getAliases()
    {
        return [];
    }
}
