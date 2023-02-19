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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Plugin\Noroute;

use Magento\Framework\App\ObjectManager;

class ChildProductUrl
{
    /**
     * @var \Bss\Simpledetailconfigurable\Helper\UrlIdentifier
     */
    protected $urlIdentifier;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * ChildProductUrl constructor.
     * @param \Bss\Simpledetailconfigurable\Helper\UrlIdentifier $urlIdentifier
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Controller\Result\ForwardFactory $forwardFactory
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\UrlIdentifier $urlIdentifier,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Controller\Result\ForwardFactory $forwardFactory
    ) {
        $this->urlIdentifier = $urlIdentifier;
        $this->productRepository = $productRepository;
        $this->resultForwardFactory = $forwardFactory;
    }

    /**
     * @param \Magento\Cms\Controller\Noroute\Index $noRoute
     * @param \Closure $proceed
     * @return \Magento\Framework\Controller\Result\Forward|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundExecute(
        \Magento\Cms\Controller\Noroute\Index $noRoute,
        \Closure $proceed
    ) {
        try {
            $redirectUrl = $this->urlIdentifier->readUrl($noRoute->getRequest()->getOriginalPathInfo());
            if (isset($redirectUrl['product']) &&
                $redirectUrl['product'] != 0) {
                $product = $this->productRepository->getById($redirectUrl['product']);
                $productStatus = $product->getStatus();
                if ($productStatus != \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED) {
                    $params = [
                        'id' => $redirectUrl['product'],
                        'category' => $redirectUrl['category']
                    ];
                    /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
                    $resultForward = $this->resultForwardFactory->create();
                    $resultForward->setParams($params);
                    $resultForward->setModule('catalog');
                    $resultForward->setController('product');
                    $resultForward->forward('view');
                    return $resultForward;
                }
            }
            return $proceed();
        } catch (\Exception $e) {
            return $proceed();
        }
    }
}
