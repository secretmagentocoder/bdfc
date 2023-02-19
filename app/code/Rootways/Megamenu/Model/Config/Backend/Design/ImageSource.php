<?php
/**
 * Mega Menu Contactus Model.
 *
 * @category  Site Search & Navigation
 * @package   Rootways_Megamenu
 * @author    Developer RootwaysInc <developer@rootways.com>
 * @copyright 2021 Rootways Inc. (https://www.rootways.com)
 * @license   Rootways Custom License
 * @link      https://www.rootways.com/pub/media/extension_doc/license_agreement.pdf
 */
namespace Rootways\Megamenu\Model\Config\Backend\Design;

class ImageSource implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [ 
            ['value' => '0', 'label' => __('Use Rootways Mega Menu Category Image')],
            ['value' => '1', 'label' => __('Use Magento Default Category Image')]
            //['value' => '2', 'label' => __('Use Magento Default Thumbnail Image')]
        ];
    }
}
