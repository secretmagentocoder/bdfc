<?php

namespace Amasty\Orderattr\Block\Checkout;

use Amasty\Orderattr\Model\ConfigProvider;
use Amasty\Orderattr\Model\Attribute\Frontend\CollectionProvider;
use Amasty\Orderattr\Model\Attribute\InputType\InputTypeProvider;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Customer\Model\Session as CustomerSession;

class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var CollectionProvider
     */
    private $collectionProvider;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var array
     */
    private $jsLayout;

    /**
     * @var InputTypeProvider
     */
    private $inputTypeProvider;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var array
     */
    private $checkoutPlaces;

    public function __construct(
        CollectionProvider $collectionProvider,
        CustomerSession $customerSession,
        InputTypeProvider $inputTypeProvider,
        ConfigProvider $configProvider,
        $checkoutPlaces = []
    ) {
        $this->collectionProvider = $collectionProvider;
        $this->configProvider = $configProvider;
        $this->inputTypeProvider = $inputTypeProvider;
        $this->customerSession = $customerSession;
        $this->checkoutPlaces = $checkoutPlaces;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     *
     * @return array
     * @throws \Exception
     */
    public function process($jsLayout)
    {
        $this->jsLayout = $jsLayout;

        $isCustomerLoggedIn = (bool)$this->customerSession->isLoggedIn()
            && $this->customerSession->getCustomer()->getAddresses();
        foreach ($this->checkoutPlaces as $checkoutPlace) {
            if (isset($checkoutPlace['isCustomerLogged'])
                && (bool)$checkoutPlace['isCustomerLogged'] !== $isCustomerLoggedIn
            ) {
                continue;
            }

            if ($attributes = $this->collectionProvider->getAttributesForStep($checkoutPlace['place_code'])) {
                $this->setJsLayoutValue(
                    $checkoutPlace['path'],
                    ['order-attributes-fields' =>
                         [
                             'component' => 'Amasty_Orderattr/js/view/order-attributes',
                             'name' => $checkoutPlace['scope'] . 'Container',
                             'amScope' => $checkoutPlace['scope'],
                             'template' => $checkoutPlace['template'],
                             'children' => $this->inputTypeProvider->getFrontendElements(
                                 $attributes,
                                 'amastyCheckoutProvider',
                                 $checkoutPlace['scope']
                             )
                         ]
                    ]
                );
            }
        }

        if ($this->configProvider->showInCheckoutProgress()) {
            $this->addAttributesToSidebar();
        }

        $this->jsLayout['components']['amastyCheckoutProvider'] = ['component' => 'uiComponent'];

        return $this->jsLayout;
    }

    /**
     * @return void
     */
    protected function addAttributesToSidebar()
    {
        $collectPlaces = [];
        foreach ($this->checkoutPlaces as $checkoutPlace) {
            if (isset($checkoutPlace['show_in_shipping_information'])
                && $checkoutPlace['show_in_shipping_information']
            ) {
                $collectPlaces[] = $checkoutPlace['scope'] . 'Container';
            }
        }
        $this->setJsLayoutValue(
            'components.checkout.children.sidebar.children.summary.children.itemsAfter.children',
            ['order-attributes-information' =>
                [
                    'component' => 'Amasty_Orderattr/js/view/order-attributes-information',
                    'displayArea' => 'shipping-information',
                    'collectPlaces' => array_unique($collectPlaces),
                    'hideEmpty' => $this->configProvider->isHideEmptyInCheckoutProgress(),
                ]
            ]
        );
    }

    /**
     * @param $keyPath
     * @param $data
     */
    protected function setJsLayoutValue($keyPath, $data)
    {
        $path = explode('.', preg_replace('/[\s\n\r]/', '', $keyPath));
        $this->jsLayoutWalker($path, $this->jsLayout, $data);
    }

    /**
     * @param $path
     * @param $layout
     * @param $data
     *
     * @return bool
     */
    protected function jsLayoutWalker($path, &$layout, &$data)
    {
        if (!count($path)) {
            return false;
        }

        $currentKey = array_shift($path);
        if (!isset($layout[$currentKey])) {
            return false;
        }

        if (empty($path)) {
            $layout[$currentKey] = array_merge($layout[$currentKey], $data);
            return true;
        }

        return $this->jsLayoutWalker($path, $layout[$currentKey], $data);
    }
}
