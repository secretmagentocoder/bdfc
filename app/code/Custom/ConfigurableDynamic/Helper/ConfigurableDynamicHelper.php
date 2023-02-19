<?php
namespace Custom\ConfigurableDynamic\Helper;


use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Framework\View\LayoutInterface;

class ConfigurableDynamicHelper
{

    private $layout;

    private $serializer;

    public function __construct(Serializer $serializer, LayoutInterface $layout)
    {
        $this->layout = $layout;
        $this->serializer = $serializer;
    }

    public function serialize($data): string
    {
        return $this->serializer->serialize($data);
    }

    public function unserialize(string $string)
    {
        return $this->serializer->unserialize($string);
    }

    public function addBlock(
        string $dynamicDataId,
        string $blockId,
        string $blockClass,
        array $config,
        Product $simpleProduct
    ): array {
        $config['dynamic'][$dynamicDataId][$simpleProduct->getId()] = [
            'value' => $this->getBlockHtml($blockId, $blockClass, $simpleProduct),
        ];

        return $config;
    }

    private function getBlockHtml(string $blockId, string $blockClass, Product $simpleProduct): string
    {
        /** @var AbstractProduct $originalBlock */
        $originalBlock = $this->layout->getBlock($blockId);

        if(!$originalBlock) {
            return '';
        }

        $block = $this->layout->createBlock($blockClass, '', ['data' => $originalBlock->getData()]);
        $block->setTemplate($originalBlock->getTemplate())
            ->setProduct($simpleProduct);

        return $block->toHtml();
    }
}