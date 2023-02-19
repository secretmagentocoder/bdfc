<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\App\Request\Http as Request;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Session\Quote as BackendQuoteSession;
use Magento\Customer\Model\Customer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Api\SearchCriteriaBuilder;

class System extends AbstractHelper
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var BackendQuoteSession
     */
    protected $backendQuoteSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var int
     */
    protected $customerGroupId;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Api\Data\GroupInterface[]
     */
    protected $customerGroups = [];

    /**
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    protected $stores = [];

    /**
     * @var Request
     */
    protected $request;

    /**
     * System constructor.
     *
     * @param Context $context
     * @param State $state
     * @param BackendQuoteSession $backendQuoteSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param HttpContext $httpContext
     * @param Session $customerSession
     * @param GroupManagementInterface $groupManagement
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param Request $request
     */
    public function __construct(
        Context $context,
        State $state,
        BackendQuoteSession $backendQuoteSession,
        CustomerRepositoryInterface $customerRepository,
        HttpContext $httpContext,
        Session $customerSession,
        GroupManagementInterface $groupManagement,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        Request $request
    ) {
        $this->state                 = $state;
        $this->storeManager          = $storeManager;
        $this->backendQuoteSession   = $backendQuoteSession;
        $this->customerRepository    = $customerRepository;
        $this->httpContext           = $httpContext;
        $this->customerSession       = $customerSession;
        $this->groupManagement       = $groupManagement;
        $this->groupRepository       = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->request               = $request;
        parent::__construct($context);
    }

    /**
     * Resolve current store id
     *
     * @return int
     */
    public function resolveCurrentStoreId()
    {
        if ($this->isAdmin()) {
            /** @var \Magento\Framework\App\RequestInterface $request */
            $request = $this->_request;
            $storeId = $request->getParam('store_id');
            if (!isset($storeId)) {
                if ($request->getControllerName() == 'order_create'
                    || $request->getFullActionName() == 'mageworx_optionbase_config_get'
                ) {
                    $storeId = $request->getParam('store');
                    if (!isset($storeId)) {
                        $storeId = $this->backendQuoteSession->getStoreId() ?: 0;
                    }
                } else {
                    $storeId = $request->getParam('store', 0);
                }
            }
            if ($storeId && is_array($storeId)) {
                $storeId = array_shift($storeId);
            }
        } else {
            $storeId = true;
        }

        $store = $this->storeManager->getStore($storeId);

        return $store->getId();
    }

    /**
     * Check if this is magento default import action
     *
     * @return boolean
     */
    public function isOptionImportAction()
    {
        return $this->isAdmin() && $this->_request->getFullActionName() === 'mui_index_render';
    }

    /**
     * Check if this is magento order create's configure quote items action
     *
     * @return boolean
     */
    public function isConfigureQuoteItemsAction()
    {
        return $this->isAdmin() && $this->_request->getFullActionName() === 'sales_order_create_configureQuoteItems';
    }

    /**
     * Check if this is magento checkout cart's configure quote items action
     *
     * @return boolean
     */
    public function isCheckoutCartConfigureAction()
    {
        return $this->_request->getFullActionName() === 'checkout_cart_configure';
    }

    /**
     * Check if this is product url with ShareableLink feature
     *
     * @return boolean
     */
    public function isShareableLink()
    {
        return $this->_request->getFullActionName() === 'catalog_product_view'
            && $this->_request->getParam('config');
    }

    /**
     * Resolve current customer group id
     *
     * @return int
     */
    public function resolveCurrentCustomerGroupId()
    {
        if ($this->customerGroupId) {
            return $this->customerGroupId;
        }

        if ($this->isAdmin()) {
            $customer              = $this->getCurrentCustomer();
            $this->customerGroupId = $customer->getGroupId();
        } else {
            $groupId = null;

            if ($this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)) {
                $this->customerGroupId = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP);
            } else {
                if ($this->customerSession->isLoggedIn()) {
                    $groupId = $this->getCurrentCustomer()->getGroupId();
                }
                $this->customerGroupId = ($groupId !== null)
                    ? $groupId
                    : $this->groupManagement->getNotLoggedInGroup()->getId();
            }
        }

        return $this->customerGroupId;
    }

    /**
     * Get current customer entity
     *
     * @return Customer
     */
    protected function getCurrentCustomer()
    {
        if ($this->isAdmin()) {
            $customerId = $this->backendQuoteSession->getCustomerId();
            if ($customerId) {
                $customer = $this->customerRepository->getById($customerId);
            } else {
                $customer = $this->customerSession->getCustomer();
            }

            return $customer;
        } else {
            return $this->customerSession->getCustomer();
        }
    }

    /**
     * Get store IDs
     *
     * @return array
     */
    public function getStoreIds()
    {
        if (!$this->stores) {
            $this->stores = $this->storeManager->getStores();
        }

        return array_keys($this->stores);
    }

    /**
     * Get customer group IDs
     *
     * @param bool $includeAllGroupIdentifier
     * @return array
     */
    public function getCustomerGroupIds($includeAllGroupIdentifier = false)
    {
        $customerGroups = [];
        if (!$this->customerGroups) {
            $this->customerGroups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        }
        foreach ($this->customerGroups as $group) {
            $customerGroups[] = $group->getId();
        }
        if ($includeAllGroupIdentifier) {
            $customerGroups[] = 32000;
            $customerGroups[] = 0;
        }

        return $customerGroups;
    }

    /**
     * Get label and value properties of stores
     *
     * @return array
     */
    public function getStores()
    {
        $stores = [];
        if (!$this->stores) {
            $this->stores = $this->storeManager->getStores();
        }
        foreach ($this->stores as $store) {
            $stores[] = [
                'label' => $store->getCode(),
                'value' => $store->getId(),
            ];
        }

        return $stores;
    }

    /**
     * Get label and value properties of customer groups
     *
     * @return array
     */
    public function getCustomerGroups()
    {
        $customerGroups = [];
        if (!$this->customerGroups) {
            $this->customerGroups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        }
        foreach ($this->customerGroups as $group) {
            $customerGroups[] = [
                'label' => $group->getCode(),
                'value' => $group->getId(),
            ];
        }

        return $customerGroups;
    }

    /**
     * Check Admin Area
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->state->getAreaCode() == Area::AREA_ADMINHTML;
    }

    /**
     * Check OrderEditor editing process in admin
     *
     * @return bool
     */
    public function isEditingByOrderEditor()
    {
        if (($this->request->getModuleName() === 'ordereditor') && $this->isAdmin()) {
            return true;
        }

        return false;
    }
}
